<?php

namespace App\Database\Traits;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
  /**
   * @var int|null
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   *
   * @JMS\Serializer\Annotation\Expose()
   */
  private $id;

  /**
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }
}
