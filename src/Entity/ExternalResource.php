<?php

namespace App\Entity;

use App\Controller\SearchController;
use App\Database\Traits\Blameable;
use App\Database\Traits\IdTrait;
use App\Database\Traits\SoftDeletable;
use App\Entity\Contracts\SearchableInterface;
use App\Entity\Contracts\StudyAreaFilteredInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExternalResource
 *
 * @author BobV
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\ExternalResourceRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class ExternalResource implements SearchableInterface, StudyAreaFilteredInterface
{
  use IdTrait;
  use Blameable;
  use SoftDeletable;

  /**
   * @var Concept[]|Collection
   *
   * @ORM\ManyToMany(targetEntity="App\Entity\Concept", mappedBy="externalResources")
   */
  private $concepts;

  /**
   * @var StudyArea|null
   *
   * @ORM\ManyToOne(targetEntity="StudyArea", inversedBy="externalResources")
   * @ORM\JoinColumn(name="study_area_id", referencedColumnName="id", nullable=false)
   *
   * @Assert\NotNull()
   */
  private $studyArea;

  /**
   * @var string
   * @ORM\Column(name="title", type="string", length=512, nullable=false)
   *
   * @Assert\NotBlank()
   * @Assert\Length(min=1, max=512)
   */
  private $title;

  /**
   * @var string|null
   *
   * @ORM\Column(name="description", type="text", nullable=true)
   *
   * @Assert\Length(max=1024)
   */
  private $description;

  /**
   * @var string|null
   *
   * @ORM\Column(name="url", type="string", length=512, nullable=true)
   *
   * @Assert\Url()
   * @Assert\Length(max=512)
   */
  private $url;

  /**
   * @var bool
   *
   * @ORM\Column(name="broken", type="boolean", nullable=false)
   *
   * @Assert\NotNull()
   */
  private $broken;

  /**
   * ExternalResource constructor.
   */
  public function __construct()
  {
    $this->title  = '';
    $this->broken = false;

    $this->concepts = new ArrayCollection();
  }

  /**
   * Searches in the external resource on the given search, returns an array with search result metadata
   *
   * @param string $search
   *
   * @return array
   */
  public function searchIn(string $search): array
  {
    // Create result array
    $results = [];

    // Search in different parts
    if (stripos($this->getTitle(), $search) !== false) {
      $results[] = SearchController::createResult(255, 'title', $this->getTitle());
    }
    if (stripos($this->getDescription(), $search) !== false) {
      $results[] = SearchController::createResult(200, 'description', $this->getDescription());
    }
    if (stripos($this->getUrl(), $search) !== false) {
      $results[] = SearchController::createResult(150, 'url', $this->getUrl());
    }

    return [
        '_data'   => $this,
        '_title'  => $this->getTitle(),
        'results' => $results,
    ];
  }

  /**
   * @return Concept[]|Collection
   */
  public function getConcepts()
  {
    return $this->concepts;
  }

  /**
   * @return string
   */
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * @param string $title
   *
   * @return ExternalResource
   */
  public function setTitle(string $title): ExternalResource
  {
    $this->title = $title;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getDescription(): ?string
  {
    return $this->description;
  }

  /**
   * @param string|null $description
   *
   * @return ExternalResource
   */
  public function setDescription(?string $description): ExternalResource
  {
    $this->description = $description;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string
  {
    return $this->url;
  }

  /**
   * @param string|null $url
   *
   * @return ExternalResource
   */
  public function setUrl(?string $url): ExternalResource
  {
    $this->url = $url;

    return $this;
  }

  /**
   * @return bool
   */
  public function isBroken(): bool
  {
    return $this->broken;
  }

  /**
   * @param bool $broken
   *
   * @return ExternalResource
   */
  public function setBroken(bool $broken): ExternalResource
  {
    $this->broken = $broken;

    return $this;
  }

  /**
   * @return StudyArea|null
   */
  public function getStudyArea(): ?StudyArea
  {
    return $this->studyArea;
  }

  /**
   * @param StudyArea $studyArea
   *
   * @return ExternalResource
   */
  public function setStudyArea(StudyArea $studyArea): ExternalResource
  {
    $this->studyArea = $studyArea;

    return $this;
  }
}
