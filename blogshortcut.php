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
            'onAdminMenu' => ['onAdminMenu', 0],
        ]);
    }

    public function onAdminTwigTemplatePaths(): void
    {
        $paths = $this->grav['twig']->twig_paths ?? [];
        array_unshift($paths, __DIR__ . '/templates');
        $this->grav['twig']->twig_paths = $paths;
    }

    public function onTwigSiteVariables(): void
    {
        $shortcut = $this->buildShortcutData();
        $buttonLabel = $shortcut['button_label'];
        $link = $shortcut['link'];

        $quickTray = $this->grav['twig']->plugins_quick_tray ?? [];
        $quickTray[] = [
            'url' => $link,
            'label' => $buttonLabel,
            'icon' => 'fa-plus',
            'authorize' => ['admin.pages.create', 'admin.super'],
            'class' => 'button button-primary button-small',
        ];
        $this->grav['twig']->plugins_quick_tray = $quickTray;

        $this->grav['twig']->twig_vars['blogshortcut'] = $shortcut;
    }

    public function onAdminMenu(): void
    {
        $shortcut = $this->buildShortcutData();
        $buttonLabel = $shortcut['button_label'];
        $link = $shortcut['link'];

        $nav = $this->grav['twig']->plugins_hooked_nav ?? [];
        $nav['blogshortcut'] = [
            'title' => $buttonLabel,
            'label' => $buttonLabel,
            'icon' => 'fa-plus',
            'url' => $link,
            'route' => 'pages/add',
            'authorize' => ['admin.pages.create', 'admin.super'],
        ];
        $this->grav['twig']->plugins_hooked_nav = $nav;
    }

    /**
     * @return array<string,string>
     */
    private function buildShortcutData(): array
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

        return [
            'link' => $link,
            'parent_route' => $parentRoute,
            'blueprint' => $blueprint,
            'button_label' => $buttonLabel,
        ];
    }
}
