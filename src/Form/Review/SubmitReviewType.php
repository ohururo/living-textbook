<?php

namespace App\Form\Review;

use App\Entity\StudyArea;
use App\Entity\UserGroup;
use App\Form\Type\SaveType;
use App\Repository\UserGroupRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SubmitReviewType extends AbstractType
{
  /**
   * @var UserGroupRepository
   */
  private $userGroupRepository;

  public function __construct(UserGroupRepository $userGroupRepository)
  {
    $this->userGroupRepository = $userGroupRepository;
  }

  /**
   * @param FormBuilderInterface $builder
   * @param array                $options
   *
   * @throws NonUniqueResultException
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $studyArea = $options['study_area'];
    assert($studyArea instanceof StudyArea);
    $userGroup = $this->userGroupRepository->getForType($studyArea, UserGroup::GROUP_REVIEWER);

    $possibleUsers = array_merge(
        [$studyArea->getOwner()],
        $userGroup ? $userGroup->getUsers()->toArray() : []
    );

    $builder
        // This field will no be rendered by the SF form component!
        ->add('pending_changes', CollectionType::class, [
            'required'      => false,
            'allow_add'     => true,
            'allow_delete'  => true,
            'entry_type'    => CollectionType::class,
            'entry_options' => [
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ],
        ])
        ->add('reviewer', ChoiceType::class, [
            'required'     => true,
            'label'        => 'review.reviewer',
            'select2'      => true,
            'choice_label' => 'selectionName',
            'choices'      => $possibleUsers,
            'help'         => 'review.reviewer-help',
            'constraints'  => [
                new NotNull(),
            ],
        ])
        ->add('submit', SaveType::class, [
            'enable_cancel'        => false,
            'enable_save_and_list' => false,
        ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver
        ->setRequired('study_area')
        ->setAllowedTypes('study_area', [StudyArea::class]);
  }

}
