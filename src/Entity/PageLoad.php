<?php

namespace App\Entity;

use App\Database\Traits\IdTrait;
use App\Entity\Contracts\StudyAreaFilteredInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Drenso\Shared\Interfaces\IdInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PageRequest.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\PageLoadRepository")
 */
class PageLoad implements StudyAreaFilteredInterface, IdInterface
{
  use IdTrait;

  /**
   * @var string
   *
   * @ORM\Column(name="user_id", type="string", length=255)
   *
   * @Assert\NotNull()
   * @Assert\NotBlank()
   */
  private $userId;

  /**
   * @var DateTime
   *
   * @ORM\Column(name="timestamp", type="datetime")
   *
   * @Assert\NotNull()
   */
  private $timestamp;

  /**
   * @var string
   *
   * @ORM\Column(name="session_id", type="guid")
   *
   * @Assert\NotNull()
   * @Assert\NotBlank()
   */
  private $sessionId;

  /**
   * @var StudyArea
   *
   * @ORM\ManyToOne(targetEntity="StudyArea")
   * @ORM\JoinColumn(name="study_area_id", referencedColumnName="id", nullable=false)
   *
   * @Assert\NotNull()
   */
  private $studyArea;

  /**
   * @var string
   *
   * @ORM\Column(name="path", type="string", length=1024)
   *
   * @Assert\NotNull()
   * @Assert\NotBlank()
   * @Assert\Length(max=1024)
   */
  private $path;

  /**
   * @var array|null
   *
   * @ORM\Column(name="path_context", type="array", nullable=true)
   *
   * @Assert\Type("array")
   */
  private $pathContext;

  /**
   * @var string|null
   *
   * @ORM\Column(name="origin", type="string", length=1024, nullable=true)
   *
   * @Assert\Length(max=1024)
   */
  private $origin;

  /**
   * @var array|null
   *
   * @ORM\Column(name="origin_context", type="array")
   *
   * @Assert\Type("array")
   */
  private $originContext;

  public function getUserId(): string
  {
    return $this->userId;
  }

  public function setUserId(string $userId): PageLoad
  {
    $this->userId = $userId;

    return $this;
  }

  public function getTimestamp(): DateTime
  {
    return $this->timestamp;
  }

  public function setTimestamp(DateTime $timestamp): PageLoad
  {
    $this->timestamp = $timestamp;

    return $this;
  }

  public function getSessionId(): string
  {
    return $this->sessionId;
  }

  public function setSessionId(string $sessionId): PageLoad
  {
    $this->sessionId = $sessionId;

    return $this;
  }

  public function getStudyArea(): StudyArea
  {
    return $this->studyArea;
  }

  public function setStudyArea(StudyArea $studyArea): PageLoad
  {
    $this->studyArea = $studyArea;

    return $this;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function setPath(string $path): PageLoad
  {
    $this->path = $path;

    return $this;
  }

  public function getPathContext(): ?array
  {
    return $this->pathContext;
  }

  public function setPathContext(?array $pathContext): PageLoad
  {
    $this->pathContext = $pathContext;

    return $this;
  }

  public function getOrigin(): ?string
  {
    return $this->origin;
  }

  public function setOrigin(?string $origin): PageLoad
  {
    $this->origin = $origin;

    return $this;
  }

  public function getOriginContext(): ?array
  {
    return $this->originContext;
  }

  public function setOriginContext(?array $originContext): PageLoad
  {
    $this->originContext = $originContext;

    return $this;
  }
}
