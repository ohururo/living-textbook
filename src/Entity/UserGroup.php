<?php

namespace App\Entity;

use App\Database\Traits\Blameable;
use App\Database\Traits\IdTrait;
use App\Database\Traits\SoftDeletable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserGroup
 *
 * @author BobV
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\UserGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class UserGroup
{

  const GROUP_REVIEWER = 'reviewer';
  const GROUP_EDITOR = 'editor';
  const GROUP_VIEWER = 'viewer';

  use IdTrait;
  use Blameable;
  use SoftDeletable;

  /**
   * @var StudyArea
   *
   * @ORM\ManyToOne(targetEntity="App\Entity\StudyArea", inversedBy="userGroups")
   * @ORM\JoinColumn(name="study_area_id", referencedColumnName="id", nullable=false)
   *
   * @Assert\NotNull()
   */
  private $studyArea;

  /**
   * @var string
   *
   * @ORM\Column(name="group_type", type="string", length=10, nullable=false)
   *
   * @Assert\NotNull()
   * @Assert\Choice(callback="getGroupTypes")
   */
  private $groupType;

  /**
   * @var User[]|Collection
   *
   * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="userGroups", fetch="EAGER")
   * @ORM\JoinTable(name="user_group_users",
   *   joinColumns={@ORM\JoinColumn(name="user_group_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
   * )
   *
   * @Assert\NotNull()
   */
  private $users;

  /**
   * UserGroup constructor.
   */
  public function __construct()
  {
    $this->groupType = self::GROUP_VIEWER;
    $this->users     = new ArrayCollection();
  }

  /**
   * Possible group types
   *
   * @return array
   */
  public static function getGroupTypes()
  {
    return [self::GROUP_REVIEWER, self::GROUP_EDITOR, self::GROUP_VIEWER];
  }

  /**
   * @return StudyArea
   */
  public function getStudyArea(): StudyArea
  {
    return $this->studyArea;
  }

  /**
   * @param StudyArea $studyArea
   *
   * @return UserGroup
   */
  public function setStudyArea(StudyArea $studyArea): UserGroup
  {
    $this->studyArea = $studyArea;

    return $this;
  }

  /**
   * @return string
   */
  public function getGroupType(): string
  {
    return $this->groupType;
  }

  /**
   * @param string $groupType
   *
   * @return UserGroup
   */
  public function setGroupType(string $groupType): UserGroup
  {
    $this->groupType = $groupType;

    return $this;
  }

  /**
   * @return User[]|Collection
   */
  public function getUsers()
  {
    return $this->users;
  }

  /**
   * @param User $user
   *
   * @return UserGroup
   */
  public function addUser(User $user): UserGroup
  {
    $this->users->add($user);

    return $this;
  }

  /**
   * @param User $user
   *
   * @return UserGroup
   */
  public function removeUser(User $user): UserGroup
  {
    $this->users->removeElement($user);

    return $this;
  }

}