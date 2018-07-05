<?php
namespace Librecores\ProjectRepoBundle\Serializer\Normalizer;

use Librecores\ProjectRepoBundle\Entity\Organization;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrganizationNormalizer implements NormalizerInterface
{
    /**
     * Normalize a Organization into a set of arrays/scalars.
     *
     * {@inheritdoc}. This class normalizes the Organizations for getting indexed in algolia
     *
     * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'name' => $object->getName(),
            'displayName' => $object->getDisplayName(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Organization;
    }
}
