<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\Contributor;
use AppBundle\Helper\DataConverter;
use AppBundle\Serializer\Serializable\Picture;
use AppBundle\Serializer\Serializable\Thumb;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContributorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Sets the owning Normalizer object.
     *
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Contributor;
    }

    public function normalize($object, $format = null, array $context = array()) : array
    {
        if (!($object instanceof Contributor)) throw new InvalidArgumentException();
        $exampleNotice = $object->getTheirMostLikedOrDisplayedNotice();

        return [
            'avatar' => !empty($object->getImage()) ?
                $this->normalizer->normalize(self::avatarWithThumbs($object), $format, $context) :
                null,
            'contributions' => $object->getNoticesCount(),
            'contribution' => [
                'example' => [
                    'matchingUrl' => $exampleNotice->getMatchingContexts()->first()->getExampleUrl(),
                    'noticeId' => $exampleNotice->getId(),
                    'noticeUrl' => $this->router->generate(
                      'app_api_getnoticeaction__invoke',
                      [ 'id' => $exampleNotice->getId() ],
                      RouterInterface::ABSOLUTE_URL),
                ],
                'numberOfPublishedNotices' => $object->getNoticesCount(),
            ],
            'id' => $object->getId(),
            'intro' => $object->getIntro() ? DataConverter::convertFullIntro($object->getIntro()) : null,
            'name' => $object->getName(),
            'ratings' => [
                'subscribes' => $object->getTotalActiveSubscriptions(),
            ],
        ];
    }

    private static function avatarWithThumbs(Contributor $contributor) : Picture
    {
        return Picture::fromContributor($contributor)
            ->addThumb(Thumb::fromName(Thumb::SMALL))
            ->addThumb(Thumb::fromName(Thumb::NORMAL))
            ->addThumb(Thumb::fromName(Thumb::LARGE));
    }
}
