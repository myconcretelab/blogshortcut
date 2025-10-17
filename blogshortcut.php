<?php

namespace Grav\Plugin;

use RocketTheme\Toolbox\Event\Event;
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
            'onBlueprintCreated' => ['onBlueprintCreated', 0],
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
        $this->grav['twig']->twig_vars['blogshortcut'] = $shortcut;

        $payload = json_encode($shortcut, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        if ($payload !== false) {
            $assets = $this->grav['assets'];
            $assets->addInlineJs(
                'window.GravBlogshortcut = ' . $payload . ';',
                ['group' => 'bottom', 'loading' => 'defer']
            );
            $assets->addJs(
                'plugin://blogshortcut/admin.min.js',
                ['group' => 'bottom', 'loading' => 'defer']
            );
        }
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
            'icon' => 'fa-plus blogshortcut-icon',
            'url' => $link,
            'route' => 'pages',
            'authorize' => ['admin.pages.create', 'admin.super'],
        ];
        $this->grav['twig']->plugins_hooked_nav = $nav;
    }

    public function onBlueprintCreated(Event $event): void
    {
        $blueprint = $event['blueprint'] ?? null;
        $type = (string) ($event['type'] ?? '');

        if (!$blueprint) {
            return;
        }

        $validTypes = [
            'admin/pages/new',
            'flex-objects/pages',
            'flex-objects/pages/page',
        ];

        if ($type !== '' && !in_array($type, $validTypes, true)) {
            return;
        }

        $shortcut = $this->buildShortcutData();
        $parentRoute = $shortcut['parent_route'];
        $blueprintName = $shortcut['blueprint'];

        if ($parentRoute !== '' && $blueprint->get('form/fields/route') !== null) {
            $blueprint->set('form/fields/route/default', $parentRoute);
            $blueprint->set('form/fields/route/value', $parentRoute);
        }

        if ($blueprintName !== '' && $blueprint->get('form/fields/name') !== null) {
            $blueprint->set('form/fields/name/default', $blueprintName);
            $blueprint->set('form/fields/name/value', $blueprintName);
        }
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
        $parentLabel = $parentRoute !== '' ? $parentRoute : '/';
        $parentTitle = '';

        if ($parentRoute !== '') {
            if (isset($this->grav['admin'])) {
                $this->grav['admin']->enablePages();
            }
            $page = $this->grav['pages']->find($parentRoute);
            if ($page !== null) {
                $parentTitle = (string) $page->title();
            }
        }

        $link = $this->getAdminBaseUrl() . '/pages';

        return [
            'link' => $link,
            'parent_route' => $parentRoute,
            'blueprint' => $blueprint,
            'button_label' => $buttonLabel,
            'parent_label' => $parentLabel,
            'parent_title' => $parentTitle,
        ];
    }

    private function getAdminBaseUrl(): string
    {
        $adminRoute = (string) $this->grav['config']->get('plugins.admin.route', '/admin');
        $adminRoute = '/' . trim($adminRoute, '/');
        if ($adminRoute === '/') {
            $adminRoute = '';
        }

        $rootUrl = rtrim($this->grav['uri']->rootUrl(true), '/');

        if ($adminRoute !== '') {
            $length = strlen($adminRoute);
            if ($length > 0 && substr($rootUrl, -$length) === $adminRoute) {
                return $rootUrl;
            }
        }

        return $rootUrl . $adminRoute;
    }
}
