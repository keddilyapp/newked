<?php


namespace App\Helpers;

use Illuminate\Support\Str;
use function abort;
use function auth;
use function request;
use function route;

class MenuWithPermission
{
    private $show_label = true;
    private $ul_class = 'nav flex-column sub-menu';
    private $li_class='';
    private $active_class='';
    private $anchor_class='nav-link';
    private $anchor_title_class='menu-title';
    private $icon_class='menu-icon';
    private $children_class='';
    private $sub_menu_class='';
    private $theme_name = '';

    public function __construct()
    {
        $theme_name = get_static_option('tenant_admin_dashboard_theme') ?? '';
        $this->theme_name = $theme_name;
    }

    public function init($ul_class,$li_class='',$active_class='',$anchor_class='',$icon_class='',$children_class='',$sub_menu_class='',$anchor_title_class='',$show_label=true)
    {
        $this->ul_class = $ul_class ?? $this->ul_class;
        $this->li_class = $li_class ?? $this->li_class;
        $this->active_class = $active_class ?? $this->active_class;
        $this->anchor_class = $anchor_class ?? $this->anchor_class;
        $this->icon_class = $icon_class ?? $this->icon_class;
        $this->children_class = $children_class ?? $this->children_class;
        $this->sub_menu_class = $sub_menu_class ?? $this->sub_menu_class;
        $this->anchor_title_class = $anchor_title_class ?? $this->anchor_title_class;
        $this->show_label = $show_label ?? $this->show_label;


        return $this;
    }

    private array $parents = [];
    private array $menus_items = [];

    public function add_menu_item(string $id, array $args = [])
    {
        if (empty($args)) {
            return;
        }

        $this->menus_items[$id] = [
            'route' => $args['route'],
            'label' =>  $args['label'],
            'parent' => $args['parent'],
            'class' =>  $args['class'] ?? '',
            'permissions' => $args['permissions'] ?? [],
            'icon' => $args['icon'] ?? '',
        ];
        if (isset($args['parent']) && !empty($args['parent'])) {
            $this->parents[] = $args['parent'];
        }
    }

    public function render_menu_items(): string
    {
        $output = '';
        $all_menu_items = $this->menus_items;

        $theme_name = get_static_option('tenant_admin_dashboard_theme') ?? '';

        foreach ($all_menu_items as $id => $item) {
            if (!$this->has_permission_to_view($item)){
                continue;
            }

            if (!$this->has_route($item) && !$this->has_sub_menu_item($id)) {
                abort(405, 'Route name required to render menu');
            }
            if ($this->is_submenu_item($item) ){
               continue;
            }

            if($theme_name === 'metronic')
            {
                $output = $this->metronicThemeMenuMarkupRender($id, $item, $output);
            }else{
                $output = $this->get_li_markup($id,$item,$output);
            }

            //  make a private method to fix code duplication issue

        }

        return $output;
    }

    private function render_sub_menus($id, $output): string
    {
        $all_menu_items = $this->get_all_submenu_by_parent_id($id);
        $output .= '<div class="collapse" id="'.Str::slug(strip_tags($id)).'">';

        $output .= '<ul class="'.$this->ul_class.'">';
        foreach ($all_menu_items as $submenu_id => $sub_menu) {
            $output .= $this->render_single_submenu_item($submenu_id, $sub_menu);

            if ($this->is_active_menu_item($sub_menu)) {
                if (isset($sub_menu['parent']) && !empty($sub_menu['parent'])){
                    $output = str_replace(
                        array(
                        'submenu-item-' . Str::slug($sub_menu['parent']),
                        'href="#'. Str::slug($sub_menu['parent']) .'" aria-expanded="false"',
                        'class="collapse" id="'.Str::slug(strip_tags($id)).'"'
                    ), array(
                        'nav-item active submenu-item-' . Str::slug($sub_menu['parent']),
                        'href="#' . Str::slug($sub_menu['parent']). '" aria-expanded="true"',
                        'class="collapse show" id="'.Str::slug(strip_tags($id)).'"'
                    ),
                        $output
                    );
                }
            }
        }
        $output .= '</ul></div>';



        return $output;
    }


    private function metronic_theme_render_sub_menus($id, $parent_title): string
    {
        // Get all submenu items for the given parent ID
        $all_menu_items = $this->get_all_submenu_by_parent_id($id);

        // Early exit if there are no submenu items
        if (count($all_menu_items) === 0) {
            return ''; // Return empty string if no submenus
        }


        // Create the submenu container (collapse)
        $submenu_output = '<div class="menu-sub menu-sub-dropdown px-2 py-4 w-250px mh-75 overflow-auto collapse" id="' . Str::slug(strip_tags($id)) . '">';

        // Assuming the first item in the array could be the parent title (or otherwise handle accordingly)
        $parent_menu_title = $parent_title;

        // Render the parent menu title
        $submenu_output .= '<div class="menu-item">';
        $submenu_output .= '<div class="menu-content">';
        $submenu_output .= '<span class="menu-section fs-5 fw-bolder ps-1 py-1">' . $parent_menu_title . '</span>';
        $submenu_output .= '</div>';
        $submenu_output .= '</div>';

        // Iterate through all submenu items
        foreach ($all_menu_items as $submenu_id => $sub_menu) {
            // Render each submenu item
            $submenu_output .= '<div class="menu-item">';
            $submenu_output .= '<a class="menu-link" href="' . ($sub_menu['route'] != '#' ? route($sub_menu['route']) : '#') . '">';

            // Render bullet icon
            $submenu_output .= '<span class="menu-bullet">';
            $submenu_output .= '<span class="bullet bullet-dot"></span>';
            $submenu_output .= '</span>';

            // Render menu title
            $submenu_output .= '<span class="menu-title">' . $sub_menu['label'] . '</span>';

            // Close the anchor tag
            $submenu_output .= '</a>';
            $submenu_output .= '</div>';

            // Check if this submenu item is active
            if ($this->is_active_menu_item($sub_menu)) {
                // Modify the submenu item class if it's active
                if (!empty($sub_menu['parent'])) {
                    // Using `str_replace` here may still cause an issue if repeated many times, so let's try to handle it differently
                    $submenu_output = str_replace(
                        [
                            'submenu-item-' . Str::slug($sub_menu['parent']),
                            'href="#' . Str::slug($sub_menu['parent']) . '" aria-expanded="false"',
                            'class="collapse" id="' . Str::slug(strip_tags($id)) . '"'
                        ],
                        [
                            'nav-item active submenu-item-' . Str::slug($sub_menu['parent']),
                            'href="#' . Str::slug($sub_menu['parent']) . '" aria-expanded="true"',
                            'class="collapse show" id="' . Str::slug(strip_tags($id)) . '"'
                        ],
                        $submenu_output
                    );
                }
            }
        }

        // Close the unordered list and submenu container
        $submenu_output .= '</div>';

        return $submenu_output; // Return the submenu markup
    }


    private function is_active_menu_item($items): bool
    {
        $route_name = $items['route'];
        if (!$this->has_route($items)) {
            return false;
        }
        return (bool)request()->routeIs($route_name);
    }

    private function has_permission_to_view($items): bool
    {
        $permissions = $items['permissions'];
        $admin_details = auth('admin')->user();

        switch ($permissions) {
            case(is_array($permissions)):
                return (bool) optional($admin_details)->canany($permissions);
                break;
            case(is_string($permissions)):
                return (bool) optional($admin_details)->can($permissions);
                break;
            default:
                return false;
                break;
        }
    }

    private function has_route($item): bool
    {
        return isset($item['route']) && !empty($item['route']);
    }

    private function has_icon($item): bool
    {
        return isset($item['icon']) && !empty($item['icon']);
    }

    private function has_label($item): bool
    {
        return isset($item['label']) && !empty($item['label']);
    }

    private function has_sub_menu_item($id): bool
    {
        return in_array($id,$this->parents);
    }

    private function get_all_submenu_by_parent_id($id): array
    {
        $all_menu_items = $this->menus_items;
        $all_submenu_items = [];
        foreach ($all_menu_items as $item_id => $item) {
            if (isset($item['parent']) && $item['parent'] === $id) {
                $all_submenu_items[$item_id] = $item;
            }
        }
        return $all_submenu_items;
    }

    private function render_single_submenu_item($submenu_id, $sub_menu): string
    {
        if (!$this->has_permission_to_view($sub_menu)) {
            return '';
        }
        if (!$this->has_route($sub_menu)) {
            abort(405, 'Route name required to render submenu');
        }
        if (!$this->is_submenu_item($sub_menu)){
            return '';
        }
        $output = '';


        if($this->theme_name === 'metronic'){
            $output .= $this->metronicThemeMenuMarkupRender($submenu_id,$sub_menu,$output);
        }else{
            $output .= $this->get_li_markup($submenu_id,$sub_menu,$output);
        }

        return $output;
    }

    private function is_submenu_item($item) : bool
    {
        return isset($item['parent']) && !is_null($item['parent']);
    }

    private function get_li_markup($id, $item, string $output) : string
    {
        $output .= '<li';
        $li_classes = ['nav-item'];
        if ($this->is_active_menu_item($item)) {
            $li_classes[] = 'active';
            if (isset($item['parent']) && !empty($item['parent'])){
                $output = str_replace('submenu-item-'.Str::slug($item['parent']),'nav-item active submenu-item-'.Str::slug($item['parent']),$output);
            }
        }
        $route = $item['route'] === '#' ?: route($item['route']);
        if ($this->has_sub_menu_item($id)) {
            $li_classes[] = 'submenu-item-'.Str::slug($id);
            $li_classes[] = $this->sub_menu_class;
            $route = 'javascript:void(0)';
        }
        $output.= ' class="'.implode(' ',$li_classes).' '.($item['class'] ?? '').' '.$this->li_class . '"';
        $output .= '>';

        $output .= '<a class="'.$this->anchor_class.'"';

        if ($this->has_sub_menu_item($id)) {
            $output .= 'data-bs-toggle="collapse"' ;
            $route = '#'.Str::slug(strip_tags($id));
        }

        $output .= 'href="'.$route.'"';
        $output .= ' aria-expanded="false">';


        if ($this->has_label($item) && $this->show_label) {
            $output .= '<span class="'.$this->anchor_title_class.'">' . $item['label'] . '</span>';
        }
        if ($this->has_sub_menu_item($id)) {
            $output .= '<i class="menu-arrow"></i>';
        }

        if ($this->has_icon($item)) {
            $output .= '<i class="' . $item['icon'] . ' '.$this->icon_class.'"></i>';
        }
        $output .= '</a>';
        if ($this->has_sub_menu_item($id)) {
            $output = $this->render_sub_menus($id, $output);
        }
        $output .= '</li>';

        return $output;
    }

    public function metronicThemeMenuMarkupRender($id, $item, string $output) : string
    {
        $output .= '<div data-kt-menu-trigger="{default: \'click\', lg: \'hover\'}" data-kt-menu-placement="right-start" class="menu-item py-2 parent_menu_item_wrapper">';

        $subMenuExist = $this->has_sub_menu_item($id);

        // Create menu link container
        $output .= '<a class="menu-link menu-center" href="'. ($item['route'] != '#' ? route($item['route']) : '#') .'">';

        // Add icon if exists
        if ($this->has_icon($item)) {
            $output .= '<span class="menu-icon me-0">';
            $output .= '<i class="ki-duotone ' . $item['icon'] . ' fs-2x">';
            $output .= '<span class="path1"></span>';
            $output .= '<span class="path2"></span>';
            $output .= '</i>';
            $output .= '</span>';
        }

        $output .= '</a>';

        // Handle submenu items (if any)
        if ($subMenuExist) {
            // Submenu items can be rendered here
            $output .= $this->metronic_theme_render_sub_menus($id, $item['label']);
        }

        // End of menu item
        $output .= '</div>';

        return $output;
    }
}


