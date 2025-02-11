<?php

namespace App\Serializer;

use App\Entity\RestrictedContext;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RestrictedContextNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RestrictedContext;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        if (!($object instanceof RestrictedContext)) {
            throw new InvalidArgumentException();
        }

        return [
            'urlRegex' => $object->getUrlRegex(),
        ];
    }
}
