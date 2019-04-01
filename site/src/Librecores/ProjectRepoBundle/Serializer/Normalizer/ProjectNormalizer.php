<?php

namespace Librecores\ProjectRepoBundle\Serializer\Normalizer;

use Librecores\ProjectRepoBundle\Entity\Project;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ProjectNormalizer
 *
 * This class normalizes the Projects for getting indexed in algolia
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
 */
class ProjectNormalizer implements NormalizerInterface
{
    /**
     * Normalize a Project into a set of arrays/scalars.
     *
     * {@inheritdoc}.
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'name' => $object->getName(),
            'displayName' => $object->getDisplayName(),
            'tagName' => $object->getTagline(),
            'dateAdded' => $object->getDateAdded(),
            'dateLastActivityOccurred' => $object->getDateLastActivityOccurred(),
            'mostUsedLanguage' => $object->getSourceRepo()->getSourceStats()->getMostUsedLanguage(),
            'parentUserName' => $object->getParentName(),
            'hierarchicalCategories' => $object->getCategoryLists(),
            'classifications' => $object->getClassificationArray(),
            'qualityScore' => $object->getQualityScore(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Project;
    }
}
