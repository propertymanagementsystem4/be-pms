<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\StoreSubmenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Requests\Menu\UpdateSubmenuRequest;
use App\Http\Requests\MenuRequest;
use App\Http\Requests\SubmenuRequest;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    use ApiResponse;
    
    public function getAllMenu()
    {
        try {
            $menus = Menu::with(['submenus' => function ($query) {
                $query->orderBy('order');
            }])->orderBy('order')->get();

            return $this->successResponse(200, $menus, 'Menus retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve menus: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve menus');
        }
    }

    public function getMenuByRoleId(Request $request)
    {
        try {
            if (!$request->user()->role_id) {
                return $this->notFoundResponse('Role not found');
            }

            $roleId = $request->user()->role_id;
            $menus = Menu::with(['submenus' => function ($query) {
                $query->orderBy('order');
            }])->where('role_id', $roleId)->orWhere('role_id', 'all')->orderBy('order')->get();

            return $this->successResponse(200, $menus, 'Menus retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve menus by user role: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve menus by user role');
        }
    }

    public function getMenuById($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid menu ID format');
            }

            $menu = Menu::with('submenus')->find($id);
            if (!$menu) {
                return $this->notFoundResponse('Menu not found');
            }

            return $this->successResponse(200, $menu, 'Menu retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve menu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve menu');
        }
    }

    public function storeMenu(StoreMenuRequest $request)
    {
        try {
            // Generate menu path
            $menuPath = '/' . Str::slug($request->name, '-');
            // Determine order (+1 from previous)
            $lastOrder = Menu::max('order') ?? 0;

            $menu = Menu::create([
                'id_menu' => Str::uuid(),
                'role_id' => $request->role_id,
                'name' => $request->name,
                'prefix' => $menuPath,
                'path' => $menuPath,
                'icon' => $request->icon,
                'order' => $lastOrder + 1,
            ]);
    
            if ($request->has('submenus')) {
                foreach ($request->submenus as $submenu) {
                    $submenuPath = $menuPath . '/' . Str::slug($submenu['name'], '-');

                    SubMenu::create([
                        'id_sub_menu' => Str::uuid(),
                        'menu_id' => $menu->id_menu,
                        'name' => $submenu['name'],
                        'path' => $submenuPath,
                        'order' => SubMenu::where('menu_id', $menu->id_menu)->max('order') + 1,
                    ]);
                }
            }

            $menu->load('submenus');

            return $this->successResponse(200, $menu, 'Menu created successfully');   
        } catch (\Exception $e) {
            Log::error('Failed to create menu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create menu');
        }
    }

    public function updateMenu(UpdateMenuRequest $request, $id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid menu ID format');
            }

            $menu = Menu::find($id);
            if (!$id) {
                return $this->notFoundResponse('Menu not found');
            }

            $menuPath = '/' . Str::slug($request->name, '-');

            $menu->update([
                'role_id' => $request->role_id,
                'name' => $request->name,
                'prefix' => $menuPath,
                'path' => $menuPath,
                'icon' => $request->icon,
                // 'order' => $request->order ?? $menu->order,
            ]);

            return $this->successResponse(200, $menu, 'Menu updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update menu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update menu');
        }
    }

    public function destroyMenu($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid menu ID format');
            }

            $menu = Menu::find($id);
            if (!$menu) {
                return $this->notFoundResponse('Menu not found');
            }

            $menu->delete();

            return $this->successResponse(200, null, 'Menu deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete menu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete menu');
        }
    }

    public function storeSubMenu(StoreSubmenuRequest $request)
    {
        try {
            $menu = Menu::find($request->menu_id);
            if (!$menu) {
                return $this->notFoundResponse('Menu not found');
            }

            $submenuPath = $menu->path . '/' . Str::slug($request->name, '-');
            // Determine order (+1 from previous)
            $lastOrder = SubMenu::where('menu_id', $menu->id_menu)->max('order') ?? 0;

            $submenu = SubMenu::create([
                'id_sub_menu' => Str::uuid(),
                'menu_id' => $request->menu_id,
                'name' => $request->name,
                'path' => $submenuPath,
                'order' => $lastOrder + 1,
            ]);

            return $this->successResponse(200, $submenu, 'SubMenu created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create submenu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create submenu');
        }
    }
    
    public function updateSubMenu(UpdateSubmenuRequest $request, $id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid submenu ID format');
            }
            
            $submenu = SubMenu::find($id);
            if (!$submenu) {
                return $this->notFoundResponse('SubMenu not found');
            }

            $validated = $request->validated();

            $menu = Menu::findOrFail($submenu->menu_id);
            $submenuPath = $menu->path . '/' . Str::slug($request->name, '-');

            $submenu->update([
                'name' => $validated['name'] ?? $submenu->name,
                'path' => $submenuPath,
                // 'order' => $request->order ?? $submenu->order,
            ]);

            return $this->successResponse(200, $submenu, 'SubMenu updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update submenu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update submenu');
        }
    }

    public function destroySubMenu($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid submenu ID format');
            }

            $submenu = SubMenu::find($id);
            if (!$submenu) {
                return $this->notFoundResponse('SubMenu not found');
            }

            $submenu->delete();

            return $this->successResponse(200, null, 'SubMenu deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete submenu: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete submenu');
        }
    }
}
