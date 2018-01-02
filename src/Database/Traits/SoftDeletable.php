<?php

namespace App\Database\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeletable
{

  /**
   * @var \DateTime
   * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
   */
  protected $deletedAt;

  /**
   * @var string
   * @ORM\Column(name="deleted_by", type="string", length=255, nullable=true)
   */
  protected $deletedBy;

  /**
   * Sets deletedAt.
   *
   * @param \Datetime|null $deletedAt
   *
   * @return $this
   */
  public function setDeletedAt(\DateTime $deletedAt = NULL)
  {
    $this->deletedAt = $deletedAt;

    return $this;
  }

  /**
   * Returns deletedAt.
   *
   * @return \DateTime
   */
  public function getDeletedAt()
  {
    return $this->deletedAt;
  }

  /**
   * Set deletedBy
   *
   * @param string $deletedBy
   *
   * @return $this
   */
  public function setDeletedBy($deletedBy)
  {
    $this->deletedBy = $deletedBy;

    return $this;
  }

  /**
   * Get deletedBy
   *
   * @return string
   */
  public function getDeletedBy()
  {
    return $this->deletedBy;
  }

  /**
   * Is deleted?
   *
   * @return bool
   */
  public function isDeleted()
  {
    return NULL !== $this->deletedAt;
  }
}
