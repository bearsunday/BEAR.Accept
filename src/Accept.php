<?php

declare(strict_types=1);


namespace BEAR\Accept;

use Aura\Accept\AcceptFactory;
use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Exception\InvalidContextKeyException;

final class Accept implements AcceptInterface
{
    /**
     * A header key for accept media type
     */
    const MEDIA_TYPE = 'Accept';

    /**
     * A header key for accept language
     */
    const LANG = 'Accept-Language';

    /**
     * Available type and lang
     *
     * @var array ['Accept' => [[$mediaType =>],...], 'Accept-Language' => [[$lang =>]],...];
     */
    private $available;

    /**
     * @Available
     */
    public function __construct(array $available)
    {
        $diff = array_diff(array_keys($available), [self::MEDIA_TYPE, self::LANG]);
        if ($diff) {
            throw new InvalidContextKeyException($diff[0]);
        }
        $this->available = $available;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $server) : array
    {
        $accept = (new AcceptFactory($server))->newInstance();
        $context = $this->getContext($accept, $server, $this->available);
        $vary = 'Accept';
        if (isset($this->available[self::LANG])) {
            $availableLang = array_keys($this->available[self::LANG]);
            $lang = $accept->negotiateLanguage($availableLang)->getValue();
            $langModule = $this->available[self::LANG][$lang];
            $context = str_replace('-app', sprintf('-%s-app', $langModule), $context);
            $vary .= ', Accept-Language';
        }

        return [$context, $vary];
    }

    private function getContext(\Aura\Accept\Accept $accept, array $server, array $defaultAvailable) : string
    {
        if (! isset($server['HTTP_ACCEPT']) && PHP_SAPI === 'cli' && isset($defaultAvailable[self::MEDIA_TYPE]['cli'])) {
            return $defaultAvailable[self::MEDIA_TYPE]['cli'];
        }
        $available = array_keys($defaultAvailable[self::MEDIA_TYPE]);
        $negotiatedMedia = $accept->negotiateMedia($available);
        $mediaValue = $negotiatedMedia === false ? $available[0] : $negotiatedMedia->getValue();
        $context = $this->available[self::MEDIA_TYPE][$mediaValue];

        return $context;
    }
}
