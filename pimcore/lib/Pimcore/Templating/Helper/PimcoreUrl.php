<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Templating\Helper;

use Pimcore\Http\RequestHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\Helper\Helper;

class PimcoreUrl extends Helper
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * @var RequestHelper
     */
    protected $requestHelper;

    /**
     * @param UrlGeneratorInterface $generator
     * @param RequestHelper $requestHelper
     */
    public function __construct(UrlGeneratorInterface $generator, RequestHelper $requestHelper)
    {
        $this->generator     = $generator;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @param array $urlOptions
     * @param null $name
     * @param bool $reset
     * @param bool $encode
     * @param bool $relative
     *
     * @return string
     */
    public function __invoke(array $urlOptions = [], $name = null, $reset = false, $encode = true, $relative = false)
    {
        // merge all parameters from request to parameters
        if (!$reset && $this->requestHelper->hasMasterRequest()) {
            $urlOptions = array_replace($this->requestHelper->getMasterRequest()->query->all(), $urlOptions);
        }

        return $this->generateUrl($name, $urlOptions, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Generate URL with support to only pass parameters ZF1 style (defaults to current route).
     *
     * @param string|null $name
     * @param array $parameters
     * @param int $referenceType
     *
     * @return string
     */
    protected function generateUrl($name = null, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        // if name is an array, treat it as parameters
        if (is_array($name)) {
            if (is_array($parameters)) {
                $parameters = array_merge($name, $parameters);
            } else {
                $parameters = $name;
            }

            $name = null;
        }

        // get name from current route
        if (null === $name) {
            $name = $this->getCurrentRoute();
        }

        if($name !== null) {
            return $this->generator->generate($name, $parameters, $referenceType);
        }

        return "";
    }

    /**
     * Tries to get the current route name from current or master request
     *
     * @return string|null
     */
    protected function getCurrentRoute()
    {
        $route = null;

        if ($this->requestHelper->hasCurrentRequest()) {
            $route = $this->requestHelper->getCurrentRequest()->attributes->get('_route');
        }

        if (!$route && $this->requestHelper->hasMasterRequest()) {
            $route = $this->requestHelper->getMasterRequest()->attributes->get('_route');
        }

        return $route;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'pimcoreUrl';
    }
}
