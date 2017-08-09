<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Accept package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Accept;

use Aura\Accept\AcceptFactory;
use BEAR\Accept\Annotation\Available;
use BEAR\Accept\Exception\InvalidContextKeyException;
use BEAR\Accept\Exception\NoAsteriskMediaTypeException;

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
        if (isset($available[self::MEDIA_TYPE]) && ! isset($available[self::MEDIA_TYPE]['*'])) {
            throw new NoAsteriskMediaTypeException;
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
        if (! isset($server['HTTP_ACCEPT'])) {
            return $defaultAvailable[self::MEDIA_TYPE]['*'];
        }
        $available = array_keys($defaultAvailable[self::MEDIA_TYPE]);
        $mediaValue = $accept->negotiateMedia($available)->getValue();
        $context = $this->available[self::MEDIA_TYPE][$mediaValue];

        return $context;
    }
}
