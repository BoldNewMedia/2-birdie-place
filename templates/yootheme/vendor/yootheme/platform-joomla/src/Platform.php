<?php

namespace YOOtheme\Joomla;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\Input\Input;
use YOOtheme\Application;
use YOOtheme\Arr;
use YOOtheme\Http\Exception;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use YOOtheme\Metadata;
use YOOtheme\Str;
use YOOtheme\Url;

class Platform
{
    /**
     * Handle application routes.
     *
     * @param Application    $app
     * @param CMSApplication $cms
     * @param Input          $input
     */
    public static function handleRoute(Application $app, CMSApplication $cms, Input $input)
    {
        $response = null;

        if ($input->getCmd('option') === 'com_ajax' && $input->get('p')) {

            // default format
            $input->def('format', 'raw');

            // get response
            $cms->registerEvent('onAfterDispatch', function () use ($app, &$response, $input) {

                // On administrator routes com_login is rendered for guest users
                if ($input->getCmd('option') !== 'com_ajax') {
                    return;
                }

                $response = $app->run(false);
            });

            // send response
            $cms->registerEvent('onAfterRender', function () use ($cms, &$response) {

                if (!$response) {
                    return;
                }

                // send headers
                if (!headers_sent()) {
                    $response->sendHeaders();
                }

                // set body for none html responses
                if (!strpos($response->getContentType(), 'html')) {
                    $cms->set('gzip', false);
                    $cms->setBody($response->getBody());
                }

                // set cms headers (fix issue when headers_sent() is still false)
                if (!headers_sent()) {
                    $cms->allowCache(true);
                    $cms->setHeader('Expires', $response->getHeaderLine('Expires'));
                    $cms->setHeader('Content-Type', $response->getContentType());
                }
            });
        }
    }

    /**
     * Handle application errors.
     *
     * @param Request    $request
     * @param Response   $response
     * @param \Exception $exception
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function handleError(Request $request, $response, $exception)
    {
        if ($exception instanceof Exception) {

            if (Str::contains($request->getHeaderLine('Accept'), 'application/json')) {
                return $response->withJson($exception->getMessage());
            }

            return $response->write($exception->getMessage());

        }

        throw $exception;
    }

    /**
     * Callback to register assets.
     *
     * @param Metadata $metadata
     * @param Document $document
     */
    public static function registerAssets(Metadata $metadata, Document $document)
    {
        foreach ($metadata->all('style:*') as $style) {

            if ($style->href) {
                $document->addStyleSheet(htmlentities(Url::to($style->href)), ['version' => $style->version], Arr::omit($style->getAttributes(), ['version', 'href', 'rel']));
            } elseif ($value = $style->getValue()) {
                $document->addStyleDeclaration($value);
            }

        }

        foreach ($metadata->all('script:*') as $script) {

            if ($script->src) {
                $document->addScript(htmlentities(Url::to($script->src)), ['version' => $script->version], Arr::omit($script->getAttributes(), ['version', 'src']));
            } elseif ($value = $script->getValue()) {

                if ($document instanceof HtmlDocument) {
                    $document->addCustomTag((string) $script);
                } else {
                    $document->addScriptDeclaration((string) $script);
                }

            }

        }
    }
}
