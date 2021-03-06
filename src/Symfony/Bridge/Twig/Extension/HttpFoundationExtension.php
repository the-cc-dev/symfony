<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UrlHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for the Symfony HttpFoundation component.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpFoundationExtension extends AbstractExtension
{
    private $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('absolute_url', [$this, 'generateAbsoluteUrl']),
            new TwigFunction('relative_path', [$this, 'generateRelativePath']),
        ];
    }

    /**
     * Returns the absolute URL for the given absolute or relative path.
     *
     * This method returns the path unchanged if no request is available.
     *
     * @return string The absolute URL
     *
     * @see Request::getUriForPath()
     */
    public function generateAbsoluteUrl(string $path)
    {
        return $this->urlHelper->getAbsoluteUrl($path);
    }

    /**
     * Returns a relative path based on the current Request.
     *
     * This method returns the path unchanged if no request is available.
     *
     * @return string The relative path
     *
     * @see Request::getRelativeUriForPath()
     */
    public function generateRelativePath(string $path)
    {
        return $this->urlHelper->getRelativePath($path);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'request';
    }
}
