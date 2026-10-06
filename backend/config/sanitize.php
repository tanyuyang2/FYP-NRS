<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/env.php";

function sanitizeHtml($html)
{
    $config = HTMLPurifier_Config::createDefault();

    /*
     * HTMLPurifier serialises its compiled definition to disk. Under XAMPP the
     * default location inside vendor/ happens to be writable; under Apache on
     * Linux it is not, so point the cache at a directory owned by the web user.
     * A custom raw definition (below) also has to be versioned, or HTMLPurifier
     * refuses to cache it.
     */
    $cachePath = nrs_env("HTMLPURIFIER_CACHE");
    if ($cachePath !== null && is_dir($cachePath) && is_writable($cachePath)) {
        $config->set("Cache.SerializerPath", $cachePath);
    } else {
        $config->set("Cache.DefinitionImpl", null);
    }

    $config->set("HTML.DefinitionID", "nrs-article-html");
    $config->set("HTML.DefinitionRev", 1);


    $config->set(
        "HTML.Allowed",
        "p,strong,i,ul,ol,li,h1,h2,h3,h4,h5,h6," .
        "br,blockquote," .
        "a[href|title|target]," .
        "figure[class]," .
        "div[data-oembed-url]," .
        "iframe[src|width|height|frameborder]," .
        "img[src|alt|width|height|style]"
    );

    $config->set("CSS.AllowedProperties", [
        "width",
        "aspect-ratio",
    ]);

    $config->set("URI.AllowedSchemes", [
        "http" => true,
        "https" => true,
    ]);

    $config->set("HTML.SafeIframe", true);

    $config->set(
        "URI.SafeIframeRegexp",
        "%^https://(www\\.)?youtube(?:-nocookie)?\\.com/embed/[A-Za-z0-9_-]+(?:\\?[^\\s]*)?$%"
    );

    /*
     * maybeGetRawDefinition() returns null when a compiled definition is
     * already cached, in which case the edits below must be skipped.
     * Calling getHTMLDefinition(true) unconditionally makes HTMLPurifier
     * refuse to cache at all: it recompiles on every call and emits a
     * warning into the error log each time.
     */
    $definition = $config->maybeGetRawHTMLDefinition();

    if ($definition !== null) {
        $definition->addElement(
            "figure",
            "Block",
            "Flow",
            "Common",
            [
                "class" => "Text",
            ]
        );

        $definition->addAttribute(
            "div",
            "data-oembed-url",
            "URI"
        );
    }

    $purifier = new HTMLPurifier($config);

    return $purifier->purify($html);
}