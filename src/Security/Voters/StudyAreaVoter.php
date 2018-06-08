<?php

namespace App\Security\Voters;

use App\Entity\StudyArea;
use App\Entity\User;
use App\Request\Wrapper\RequestStudyArea;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudyAreaVoter extends Voter
{

  // Role constants
  const OWNER = 'STUDYAREA_OWNER';
  const SHOW = 'STUDYAREA_SHOW';
  const EDIT = 'STUDYAREA_EDIT';

  /** @var AccessDecisionManagerInterface */
  private $decisionManager;

  /**
   * StudyAreaVoter constructor.
   *
   * @param AccessDecisionManagerInterface $decisionManager
   */
  public function __construct(AccessDecisionManagerInterface $decisionManager)
  {
    $this->decisionManager = $decisionManager;
  }

  /**
   * Determines if the attribute and subject are supported by this voter.
   *
   * @param string $attribute An attribute
   * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
   *
   * @return bool True if the attribute and subject are supported, false otherwise
   */
  protected function supports($attribute, $subject)
  {
    if (!in_array($attribute, $this->supportedAttributes())) {
      return false;
    }

    if ($subject !== NULL && !$subject instanceof StudyArea && !$subject instanceof RequestStudyArea) {
      return false;
    }

    return true;
  }

  /**
   * Perform a single access check operation on a given attribute, subject and token.
   * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
   *
   * @param string         $attribute
   * @param mixed          $subject
   * @param TokenInterface $token
   *
   * @return bool
   */
  protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
  {
    $user = $token->getUser();

    if (!$user instanceof User) {
      // Require authenticated user
      return false;
    }

    if ($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])){
      return true;
    }

    // Convert study area if required
    if ($subject instanceof RequestStudyArea) {
      // Check for value, otherwise deny access
      if (!$subject->hasValue()) return false;

      $subject = $subject->getStudyArea();
    }

    /** @var StudyArea $subject */
    assert($subject instanceof StudyArea);

    // Always return false for null values
    if ($subject === NULL) {
      return false;
    }

    switch ($attribute) {
      case self::OWNER:
        return $subject->isOwner($user);
      case self::SHOW:
        return $subject->isVisible($user);
      case self::EDIT:
        return $subject->isEditable($user);
    }

    throw new \LogicException('This code should not be reached!');
  }

  /**
   * @return array
   */
  private function supportedAttributes()
  {
    return [
        self::OWNER,
        self::SHOW,
        self::EDIT,
    ];
  }
}