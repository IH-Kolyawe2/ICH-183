<?php

namespace App\Libs;

class TwigBlacklistedTagExtension extends \Twig\Extension\AbstractExtension
{
    private $blacklistedTags;
    private const DEFAULT_BLACKLISTED_TAGS = ['script'];

    public function __construct(array $blacklistedTags = null)
    {
        $this->blacklistedTags = $blacklistedTags ?? self::DEFAULT_BLACKLISTED_TAGS;
    }

    public function getName()
    {
        return 'filter_blacklisted_tags_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('blacklisttags', [$this, 'removeBlackListedTags'], ['is_safe' => ['html']])
        ];
    }

    public function removeBlackListedTags($html)
    {
        $tags = implode('|', $this->blacklistedTags);
        $html = preg_replace('/(<\/?.*(' . $tags . ').*\/?>)/', '', $html);

        return $html;
    }
}