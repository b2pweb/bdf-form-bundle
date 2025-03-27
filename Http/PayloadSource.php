<?php

namespace Bdf\Form\Bundle\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * The source of the payload in a request.
 */
enum PayloadSource
{
    /**
     * Auto-detect the source based on the request method.
     *
     * If the method is POST, PUT, or PATCH, it will use the body.
     * Otherwise, it will use the query string.
     */
    case Auto;

    /**
     * Extract the payload from the query string.
     *
     * @see Request::$query
     */
    case QueryString;

    /**
     * Extract the payload from the request body.
     *
     * @see Request::getPayload()
     */
    case Body;

    /**
     * Extract the payload from the request attributes.
     *
     * @see Request::$attributes
     */
    case Attributes;

    /**
     * Extract the payload from the request.
     */
    public function extract(Request $request): array
    {
        return match ($this) {
            self::Auto => self::extractFromHttpMethod($request),
            self::QueryString => $request->query->all(),
            self::Body => $request->getPayload()->all(),
            self::Attributes => $request->attributes->all(),
        };
    }

    private static function extractFromHttpMethod(Request $request): array
    {
        return match ($request->getMethod()) {
            'POST', 'PUT', 'PATCH' => $request->getPayload()->all(),
            default => $request->query->all(),
        };
    }
}
