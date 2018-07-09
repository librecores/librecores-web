<?php
namespace Librecores\ProjectRepoBundle\Serializer\Normalizer;

use Librecores\ProjectRepoBundle\Entity\Organization;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class OrganizationNormalizer
 *
 * This class normalizes the Organizations for getting indexed in algolia
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
 *
 * @package Librecores\ProjectRepoBundle\Serializer\Normalizer
 */
class OrganizationNormalizer implements NormalizerInterface
{
    /**
     * Normalize a Organization into a set of arrays/scalars.
     *
     * {@inheritdoc}.
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
