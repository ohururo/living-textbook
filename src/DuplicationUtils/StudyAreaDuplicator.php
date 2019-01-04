<?php

namespace App\DuplicationUtils;

use App\Entity\Abbreviation;
use App\Entity\Concept;
use App\Entity\ConceptRelation;
use App\Entity\ExternalResource;
use App\Entity\LearningOutcome;
use App\Entity\RelationType;
use App\Entity\StudyArea;
use App\Repository\AbbreviationRepository;
use App\Repository\ConceptRelationRepository;
use App\Repository\ExternalResourceRepository;
use App\Repository\LearningOutcomeRepository;
use App\UrlUtils\Model\Url;
use App\UrlUtils\Model\UrlContext;
use App\UrlUtils\UrlScanner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

class StudyAreaDuplicator
{
  /** @var UrlContext */
  private $urlContext;

  /** @var string */
  private $uploadsPath;

  /** @var EntityManagerInterface */
  private $em;

  /** @var UrlScanner */
  private $urlScanner;

  /** @var RouterInterface */
  private $router;

  /** @var AbbreviationRepository */
  private $abbreviationRepo;

  /** @var ConceptRelationRepository */
  private $conceptRelationRepo;

  /** @var ExternalResourceRepository */
  private $externalResourceRepo;

  /** @var LearningOutcomeRepository */
  private $learningOutcomeRepo;

  /** @var StudyArea */
  private $studyAreaToDuplicate;

  /** @var StudyArea */
  private $newStudyArea;

  /** @var Concept[] */
  private $concepts;

  /** @var LearningOutcome[] Array of duplicated learning outcomes ([original id] = new learning outcome) */
  private $newLearningOutcomes = [];

  /** @var ExternalResource[] Array of duplicated external resources ([original id] = new external resource) */
  private $newExternalResources = [];

  /** @var Abbreviation[] Array of duplicated abbreviations ([original id] = new abbreviation) */
  private $newAbbreviations = [];

  /** @var Concept[] Array of duplicated concepts ([original id] = new concept) */
  private $newConcepts = [];

  /**
   * StudyAreaDuplicator constructor.
   *
   * @param string                     $projectDir
   * @param EntityManagerInterface     $em
   * @param UrlScanner                 $urlScanner
   * @param RouterInterface            $router
   * @param AbbreviationRepository     $abbreviationRepo
   * @param ConceptRelationRepository  $conceptRelationRepo
   * @param ExternalResourceRepository $externalResourceRepo
   * @param LearningOutcomeRepository  $learningOutcomeRepo
   *
   * @param StudyArea                  $studyAreaToDuplicate Study area to duplicate
   * @param StudyArea                  $newStudyArea         New study area
   * @param Concept[]                  $concepts             Concepts to copy
   */
  public function __construct(string $projectDir, EntityManagerInterface $em, UrlScanner $urlScanner, RouterInterface $router,
                              AbbreviationRepository $abbreviationRepo, ConceptRelationRepository $conceptRelationRepo,
                              ExternalResourceRepository $externalResourceRepo, LearningOutcomeRepository $learningOutcomeRepo,
                              StudyArea $studyAreaToDuplicate, StudyArea $newStudyArea, array $concepts)
  {
    $this->urlContext           = new UrlContext(self::class);
    $this->uploadsPath          = $projectDir . '/public/uploads/studyarea';
    $this->em                   = $em;
    $this->urlScanner           = $urlScanner;
    $this->router               = $router;
    $this->abbreviationRepo     = $abbreviationRepo;
    $this->conceptRelationRepo  = $conceptRelationRepo;
    $this->externalResourceRepo = $externalResourceRepo;
    $this->learningOutcomeRepo  = $learningOutcomeRepo;
    $this->studyAreaToDuplicate = $studyAreaToDuplicate;
    $this->newStudyArea         = $newStudyArea;
    $this->concepts             = $concepts;
  }

  /**
   * Duplicates the given study area into the new study area
   *
   * @throws \Exception
   */
  public function duplicate()
  {
    $this->em->getConnection()->beginTransaction();
    try {
      // Persist the new study area, and flush to retrieve id
      $this->em->persist($this->newStudyArea);
      $this->em->flush();

      // Duplicate the study area learning outcomes
      $this->duplicateLearningOutcomes();

      // Duplicate the study area external resources
      $this->duplicateExternalResources();

      // Duplicate the study area abbreviations
      $this->duplicateAbbreviations();

      // Duplicate the concepts
      $this->duplicateConcepts();

      // Duplicate the relations and relation types for the study area
      $this->duplicateRelations();

      // Flush to generate id's for the links
      $this->em->flush();

      // Scan the links
      $this->scanLinks();

      // Duplicate the uploads
      $this->duplicateUploads();

      // Save the final data
      $this->em->flush();

      $this->em->getConnection()->commit();
    } catch (\Exception $e) {
      $this->removeUploads();
      $this->em->getConnection()->rollBack();
      throw $e;
    }
  }

  /**
   * Duplicate the learning outcomes
   */
  private function duplicateLearningOutcomes(): void
  {
    $learningOutcomes = $this->learningOutcomeRepo->findForConcepts($this->concepts);
    foreach ($learningOutcomes as $learningOutcome) {
      $newLearningOutcome = (new LearningOutcome())
          ->setStudyArea($this->newStudyArea)
          ->setNumber($learningOutcome->getNumber())
          ->setName($learningOutcome->getName())
          ->setText($learningOutcome->getText());

      $this->em->persist($newLearningOutcome);
      $this->newLearningOutcomes[$learningOutcome->getId()] = $newLearningOutcome;
    }
  }

  /**
   * Duplicate the external resources
   */
  private function duplicateExternalResources(): void
  {
    $externalResources = $this->externalResourceRepo->findForConcepts($this->concepts);
    foreach ($externalResources as $externalResource) {
      $newExternalResource = (new ExternalResource())
          ->setStudyArea($this->newStudyArea)
          ->setTitle($externalResource->getTitle())
          ->setDescription($this->updateUrls($externalResource->getDescription()))
          ->setUrl($this->updateUrls($externalResource->getUrl()))
          ->setBroken($externalResource->isBroken());

      $this->em->persist($newExternalResource);
      $this->newExternalResources[$externalResource->getId()] = $newExternalResource;
    }
  }


  /**
   * Duplicate the abbreviations
   */
  private function duplicateAbbreviations(): void
  {
    $abbreviations = $this->abbreviationRepo->findForStudyArea($this->studyAreaToDuplicate);
    foreach ($abbreviations as $abbreviation) {
      $newAbbreviation = (new Abbreviation())
          ->setStudyArea($this->newStudyArea)
          ->setAbbreviation($abbreviation->getAbbreviation())
          ->setMeaning($abbreviation->getMeaning());

      $this->em->persist($newAbbreviation);
      $this->newAbbreviations[$abbreviation->getId()] = $newAbbreviation;
    }
  }

  /**
   * Duplicate the concepts
   */
  private function duplicateConcepts(): void
  {
    $priorKnowledges = [];
    foreach ($this->concepts as $concept) {
      $newConcept = new Concept();
      $newConcept
          ->setName($concept->getName())
          ->setIntroduction($newConcept->getIntroduction()->setText($concept->getIntroduction()->getText()))
          ->setSynonyms($concept->getSynonyms())
          ->setTheoryExplanation($newConcept->getTheoryExplanation()->setText($concept->getTheoryExplanation()->getText()))
          ->setHowTo($newConcept->getHowTo()->setText($concept->getHowTo()->getText()))
          ->setExamples($newConcept->getExamples()->setText($concept->getExamples()->getText()))
          ->setSelfAssessment($newConcept->getSelfAssessment()->setText($concept->getSelfAssessment()->getText()))
          ->setStudyArea($this->newStudyArea);

      // Set learning outcomes
      foreach ($concept->getLearningOutcomes() as $oldLearningOutcome) {
        $newConcept->addLearningOutcome($this->newLearningOutcomes[$oldLearningOutcome->getId()]);
      }

      // Set external resources
      foreach ($concept->getExternalResources() as $oldExternalResource) {
        $newConcept->addExternalResource($this->newExternalResources[$oldExternalResource->getId()]);
      }

      // Save current prior knowledge to update them later when the concept map is complete
      $priorKnowledges[$concept->getId()] = $concept->getPriorKnowledge();

      $this->newConcepts[$concept->getId()] = $newConcept;
      $this->em->persist($newConcept);
    }

    // Loop the concepts again to add the prior knowledge
    foreach ($this->newConcepts as $oldId => &$newConcept) {
      foreach ($priorKnowledges[$oldId] as $priorKnowledge) {
        /** @var Concept $priorKnowledge */
        if (array_key_exists($priorKnowledge->getId(), $this->newConcepts)) {
          $newConcept->addPriorKnowledge($this->newConcepts[$priorKnowledge->getId()]);
        }
      }
    }
  }

  /**
   * Duplicate the relations
   */
  private function duplicateRelations(): void
  {
    $conceptRelations = $this->conceptRelationRepo->findByConcepts($this->concepts);
    $newRelationTypes = [];
    foreach ($conceptRelations as $conceptRelation) {
      // Skip relation for concepts that aren't duplicated
      if (!array_key_exists($conceptRelation->getSource()->getId(), $this->newConcepts)
          || !array_key_exists($conceptRelation->getTarget()->getId(), $this->newConcepts)) {
        continue;
      }

      $relationType = $conceptRelation->getRelationType();

      // Duplicate relation type, if not done yet
      if (!array_key_exists($relationType->getId(), $newRelationTypes)) {
        $newRelationType = (new RelationType())
            ->setStudyArea($this->newStudyArea)
            ->setName($relationType->getName());

        $newRelationTypes[$relationType->getId()] = $newRelationType;
        $this->em->persist($newRelationType);
      }

      // Duplicate relation
      $newConceptRelation = (new ConceptRelation())
          ->setSource($this->newConcepts[$conceptRelation->getSource()->getId()])
          ->setTarget($this->newConcepts[$conceptRelation->getTarget()->getId()])
          ->setRelationType($newRelationTypes[$relationType->getId()])
          ->setIncomingPosition($conceptRelation->getIncomingPosition())
          ->setOutgoingPosition($conceptRelation->getOutgoingPosition());

      $this->em->persist($newConceptRelation);
    }
  }

  /**
   * Scan for links in the newly duplicated data
   */
  private function scanLinks(): void
  {
    // Check for null
    if ($this->newStudyArea->getId() === NULL) {
      throw new \InvalidArgumentException('New study area id is NULL!');
    }

    // Update learning outcomes
    foreach ($this->newLearningOutcomes as $newLearningOutcome) {
      $newLearningOutcome->setText($this->updateUrls($newLearningOutcome->getText()));
    }

    // Update external resources
    foreach ($this->newExternalResources as $newExternalResource) {
      $newExternalResource
          ->setDescription($this->updateUrls($newExternalResource->getDescription()))
          ->setUrl($this->updateUrls($newExternalResource->getUrl()));
    }

    // Update concepts
    foreach ($this->newConcepts as $newConcept) {
      $newConcept->getIntroduction()->setText(
          $this->updateUrls($newConcept->getIntroduction()->getText()));
      $newConcept->getTheoryExplanation()->setText(
          $this->updateUrls($newConcept->getTheoryExplanation()->getText()));
      $newConcept->getHowTo()->setText(
          $this->updateUrls($newConcept->getHowTo()->getText()));
      $newConcept->getExamples()->setText(
          $this->updateUrls($newConcept->getExamples()->getText()));
      $newConcept->getSelfAssessment()->setText(
          $this->updateUrls($newConcept->getSelfAssessment()->getText()));
    }

  }

  /**
   * Duplicates the uploads directory, if any
   */
  private function duplicateUploads(): void
  {
    $fileSystem = new Filesystem();
    $source     = $this->getStudyAreaDirectory($this->studyAreaToDuplicate);

    if (!$fileSystem->exists($source)) {
      // Nothing to duplicate
      return;
    }

    $fileSystem->mirror($source, $this->getStudyAreaDirectory($this->newStudyArea));
  }

  /**
   * Removes the uploads directory, if any
   */
  private function removeUploads(): void
  {
    $fileSystem = new Filesystem();
    $directory  = $this->getStudyAreaDirectory($this->newStudyArea);
    if (!$fileSystem->exists($directory)) {
      // Nothing to remove
      return;
    }

    $fileSystem->remove($directory);
  }

  /**
   * Retrieve the study area uploads path
   *
   * @param StudyArea $studyArea
   *
   * @return string
   */
  private function getStudyAreaDirectory(StudyArea $studyArea)
  {
    // Check for null
    if ($studyArea->getId() === NULL) {
      throw new \InvalidArgumentException('Study area id is NULL!');
    }

    return sprintf('%s/%d', $this->uploadsPath, $studyArea->getId());
  }

  /**
   * Replaces study area urls with the id of the area to duplicate with the id of the new study area
   *
   * @param string|null $text
   *
   * @return string|null
   */
  private function updateUrls(?string $text): ?string
  {
    if ($text === NULL) {
      return NULL;
    }

    // Scan for urls
    $urls = $this->urlScanner->scanText($text);
    foreach ($urls as $url) {
      if (!$url->isInternal()) {
        // If not internal, skip
        continue;
      }

      if (false == ($matchedRouteData = $this->matchPath($url->getPath()))) {
        continue;
      }

      // Retrieve matched route
      $routeName = $matchedRouteData['_route'];

      // Check for _home matches
      $homePath = false;
      if ($routeName === '_home') {
        $homePath = true;

        // Redo matching on url without '/page/'
        $cleanPath = str_replace('/page', '', $url->getPath());
        if (false == ($matchedRouteData = $this->matchPath($cleanPath))) {
          continue;
        }

        // Retrieve new route
        $routeName = $matchedRouteData['_route'];
      }

      // Check if this url is actually from the area to duplicate
      if (intval($matchedRouteData['_studyArea']) !== $this->studyAreaToDuplicate->getId()) {
        continue;
      }

      // Update route parameters
      unset($matchedRouteData['_route']);
      unset($matchedRouteData['_controller']);
      $matchedRouteData['_studyArea'] = $this->newStudyArea->getId();

      // Update route parameters for specific routes
      if ($routeName === 'app_concept_show') {
        // Check whether the new concept is available
        if (array_key_exists(intval($matchedRouteData['concept']), $this->newConcepts)) {
          $matchedRouteData['concept'] = $this->newConcepts[intval($matchedRouteData['concept'])]->getId();
        } else {
          // Revert to old study area id to not break link completely
          $matchedRouteData['_studyArea'] = $this->studyAreaToDuplicate->getId();
        }
      } else if ($routeName === 'app_learningoutcome_show') {
        // Check whether the new learning outcome is available
        if (array_key_exists(intval($matchedRouteData['learningOutcome']), $this->newLearningOutcomes)) {
          $matchedRouteData['learningOutcome'] = $this->newLearningOutcomes[intval($matchedRouteData['learningOutcome'])]->getId();
        } else {
          // Revert to old study area id to not break link completely
          $matchedRouteData['_studyArea'] = $this->studyAreaToDuplicate->getId();
        }
      }

      // Generate new url
      $newUrl = new Url(
          $this->router->generate($routeName, $matchedRouteData,
              $url->isPath() || $homePath ? RouterInterface::ABSOLUTE_PATH : RouterInterface::ABSOLUTE_URL),
          true, $this->urlContext
      );


      // Regenerate route again if _home route was detected
      if ($homePath) {
        $newUrl = new Url(
            $this->router->generate('_home_simple', ['pageUrl' => ltrim($newUrl->getPath(), '/')],
                $url->isPath() ? RouterInterface::ABSOLUTE_PATH : RouterInterface::ABSOLUTE_URL),
            true, $this->urlContext);
      }

      // Replace url in text
      $text = str_replace($url->getUrl(), $newUrl->getUrl(), $text);
    }

    // Scan for data attributes
    $text = $this->updateDataAttributes($text, 'concept', $this->newConcepts);
    $text = $this->updateDataAttributes($text, 'abbr', $this->newAbbreviations);

    return $text;
  }

  /**
   * Replace data-*-id attributes with the new ids in the new study area
   *
   * @param string $text
   * @param string $attribute
   * @param array  $source
   *
   * @return string
   */
  private function updateDataAttributes(string $text, string $attribute, array &$source): string
  {
    $pattern = '/(?i)data-' . preg_quote($attribute) . '-id\s*=\s*["\']\s*(\d+)\s*["\']/';
    $matches = [];
    if (false !== preg_match_all($pattern, $text, $matches) &&
        isset($matches[0]) && isset($matches[1])) {
      // Regex search successful
      foreach ($matches[1] as $key => $match) {
        // Find new id
        if (array_key_exists(intval($match), $source)) {
          $replace = str_replace($match, $source[intval($match)]->getId(), $matches[0][$key]);
          $text    = str_replace($matches[0][$key], $replace, $text);
        }
      }
    }

    return $text;
  }

  /**
   * Try to match the given path with the internal routing
   *
   * @param string $path
   *
   * @return bool|array
   */
  private function matchPath(string $path)
  {
    // Test if url actually matches an internal route
    try {
      $matchedRoute = $this->router->match($path);
    } catch (\RuntimeException $e) {
      if ($e instanceof ExceptionInterface) {
        // Route couldn't be matched internally
        return false;
      }

      throw $e;
    }

    // If _route or _studyArea is not defined, no action is required
    if (!array_key_exists('_route', $matchedRoute) ||
        !array_key_exists('_studyArea', $matchedRoute)) {
      return false;
    }

    return $matchedRoute;
  }
}