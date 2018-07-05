<?php
namespace Librecores\ProjectRepoBundle\Serializer\Normalizer;

use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface
{
    /**
     * Normalize a user into a set of arrays/scalars.
     *
     * {@inheritdoc}. This class normalize the users for getting indexed in algolia.
     *
     * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'name' => $object->getName(),
            'username' => $object->getUsername(),
            'createdAt' => $object->getCreatedAt(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof User;
    }
}
