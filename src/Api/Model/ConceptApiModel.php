<?php

namespace App\Api\Model;

use App\Entity\Concept;
use App\Entity\ConceptRelation;
use App\Entity\Tag;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class ConceptApiModel
{
  protected function __construct(
      protected readonly int $id,
      #[Groups(['Default', 'mutate'])]
      protected readonly string $name,
      #[Groups(['Default', 'mutate'])]
      protected readonly string $definition,
      #[Groups(['Default', 'mutate'])]
      protected readonly string $synonyms,
      #[OA\Property(description: 'Tag id list', type: 'array', items: new OA\Items(type: 'number'))]
      protected readonly array $tags,
      #[OA\Property(type: 'array', items: new OA\Items(new Model(type: ConceptRelationApiModel::class)))]
      protected readonly array $outgoingRelations,
      #[OA\Property(description: 'Specific Dotron configuration for a concept, only returned when Dotron is been enabled', type: 'object', nullable: true)]
      #[Type('array')]
      #[Groups(['dotron'])]
      protected readonly ?array $dotronConfig
  ) {
  }

  public static function fromEntity(Concept $concept): self
  {
    return new self(
        $concept->getId(),
        $concept->getName(),
        $concept->getDefinition(),
        $concept->getSynonyms(),
        $concept->getTags()->map(fn (Tag $tag) => $tag->getId())->getValues(),
        $concept->getOutgoingRelations()
            ->map(fn (ConceptRelation $conceptRelation) => ConceptRelationApiModel::fromEntity($conceptRelation))
            ->getValues(),
        $concept->getDotronConfig()
    );
  }

  public function mapToEntity(?Concept $concept): Concept
  {
    return ($concept ?? new Concept())
        ->setName($this->name ?? $concept?->getName() ?? '')
        ->setDefinition($this->definition ?? $concept?->getDefinition() ?? '')
        ->setSynonyms($this->synonyms ?? $concept?->getSynonyms() ?? '')
        ->setDotronConfig($this->dotronConfig ?? $concept?->getDotronConfig() ?? null);
  }
}
