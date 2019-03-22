<?php

namespace App\Controller;


use App\Entity\Concept;
use App\Entity\LearningOutcome;
use App\Entity\LearningPath;
use App\Export\Provider\RdfProvider;
use App\Request\Wrapper\RequestStudyArea;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResourceController
 *
 * @Route("/resource/{_studyArea}", requirements={"_studyArea"="\d+"})
 */
class ResourceController extends AbstractController
{
  /**
   * @Route("/concept/{concept}", requirements={"concept"="\d+"})
   * @IsGranted("STUDYAREA_SHOW", subject="requestStudyArea")
   *
   * @param RequestStudyArea $requestStudyArea
   * @param Concept          $concept
   * @param RdfProvider      $provider
   *
   * @return JsonResponse
   * @throws \EasyRdf_Exception
   */
  public function concept(RequestStudyArea $requestStudyArea, Concept $concept, RdfProvider $provider): JsonResponse
  {
    if ($concept->getStudyArea()->getId() !== $requestStudyArea->getStudyArea()->getId()) throw $this->createNotFoundException();
    $graph = new \EasyRdf_Graph($provider->generateConceptResourceUrl($concept));
    $provider->addConceptResource($concept, $graph);

    return $provider->exportGraph($graph);
  }

  /**
   * @Route("/learningpath/{learningPath}", requirements={"learningPath"="\d+"})
   * @IsGranted("STUDYAREA_SHOW", subject="requestStudyArea")
   *
   * @param RequestStudyArea $requestStudyArea
   * @param LearningPath     $learningPath
   * @param RdfProvider      $provider
   *
   * @return JsonResponse
   * @throws \EasyRdf_Exception
   */
  public function learningPath(RequestStudyArea $requestStudyArea, LearningPath $learningPath, RdfProvider $provider): JsonResponse
  {
    if ($learningPath->getStudyArea()->getId() !== $requestStudyArea->getStudyArea()->getId()) throw $this->createNotFoundException();
    $graph = new \EasyRdf_Graph($provider->generateLearningPathResourceUrl($learningPath));
    $provider->addLearningPathResource($learningPath, $graph);

    return $provider->exportGraph($graph);
  }

  /**
   * @Route("/learningoutcome/{learningOutcome}", requirements={"learningOutcome"="\d+"})
   * @IsGranted("STUDYAREA_SHOW", subject="requestStudyArea")
   *
   * @param RequestStudyArea $requestStudyArea
   * @param LearningOutcome  $learningOutcome
   * @param RdfProvider      $provider
   *
   * @return JsonResponse
   * @throws \EasyRdf_Exception
   */
  public function learningOutcome(RequestStudyArea $requestStudyArea, LearningOutcome $learningOutcome, RdfProvider $provider): JsonResponse
  {
    if ($learningOutcome->getStudyArea()->getId() !== $requestStudyArea->getStudyArea()->getId()) throw $this->createNotFoundException();
    $graph = new \EasyRdf_Graph($provider->generateLearningOutcomeResourceUrl($learningOutcome));
    $provider->addLearningOutcomeResource($learningOutcome, $graph);

    return $provider->exportGraph($graph);
  }

  /**
   * @Route("/studyarea")
   * @IsGranted("STUDYAREA_SHOW", subject="requestStudyArea")
   *
   * @param RequestStudyArea $requestStudyArea
   * @param RdfProvider      $provider
   *
   * @return Response
   * @throws \EasyRdf_Exception
   */
  public function studyArea(RequestStudyArea $requestStudyArea, RdfProvider $provider): Response
  {
    $studyArea = $requestStudyArea->getStudyArea();

    return $provider->exportStudyArea($studyArea);
  }
}