<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;

class BlogshortcutPlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        if (!$this->config->get('plugins.blogshortcut.enabled', true)) {
            return;
        }

        $this->enable([
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
        ]);
    }

    public function onAdminTwigTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigSiteVariables(): void
    {
        $config = (array) $this->config->get('plugins.blogshortcut', []);

        $parentRoute = trim((string) ($config['parent_route'] ?? ''), '/');
        $parentRoute = $parentRoute !== '' ? '/' . $parentRoute : '';

        $blueprint = (string) ($config['blueprint'] ?? 'item');
        $buttonLabel = (string) ($config['button_label'] ?? 'Nouvel article');

        $params = [
            'blueprint' => $blueprint,
        ];

        if ($parentRoute !== '') {
            $params['parent'] = $parentRoute;
        }

        $query = http_build_query($params);

        $link = rtrim($this->grav['uri']->rootUrl(true), '/') . '/admin/pages/add';
        if ($query !== '') {
            $link .= '?' . $query;
        }

        $this->grav['twig']->twig_vars['blogshortcut'] = [
            'link' => $link,
            'parent_route' => $parentRoute,
            'blueprint' => $blueprint,
            'button_label' => $buttonLabel,
        ];
    }
}
