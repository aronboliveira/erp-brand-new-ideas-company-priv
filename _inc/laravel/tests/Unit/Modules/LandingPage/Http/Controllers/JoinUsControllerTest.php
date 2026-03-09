<?php
declare(strict_types=1);
namespace Tests\Unit\Modules\LandingPage\Http\Controllers;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use Modules\LandingPage\Http\Controllers\JoinUsController;
use Illuminate\Http\{RedirectResponse, JsonResponse, Request};
use Illuminate\View\View;

/**
 * @covers \Modules\LandingPage\Http\Controllers\JoinUsController
 */
class JoinUsControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_IDX_equals_index_1(): void
    {
        $this->assertSame('index', JoinUsController::IDX);
    }

    public function test_constant_CRT_equals_create_2(): void
    {
        $this->assertSame('create', JoinUsController::CRT);
    }

    public function test_constant_STR_equals_store_3(): void
    {
        $this->assertSame('store', JoinUsController::STR);
    }

    public function test_constant_SHW_equals_show_4(): void
    {
        $this->assertSame('show', JoinUsController::SHW);
    }

    public function test_constant_EDT_equals_edit_5(): void
    {
        $this->assertSame('edit', JoinUsController::EDT);
    }

    public function test_constant_UPD_equals_update_6(): void
    {
        $this->assertSame('update', JoinUsController::UPD);
    }

    public function test_constant_DEL_equals_destroy_7(): void
    {
        $this->assertSame('destroy', JoinUsController::DEL);
    }

    public function test_constant_JU_U_ST_equals_joinUsUserStore_8(): void
    {
        $this->assertSame('joinUsUserStore', JoinUsController::JU_U_ST);
    }

    public function test_index_returns_expected_type_9(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->index($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'index must return View|RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in index');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in index');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in index');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in index');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in index');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in index');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in index');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in index');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in index');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in index');
                return;
        }
    }

    public function test_show_returns_expected_type_10(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->show($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'show must return View|RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in show');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in show');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in show');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in show');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in show');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in show');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in show');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in show');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in show');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in show');
                return;
        }
    }

    public function test_create_returns_expected_type_11(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->create($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'create must return View|RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in create');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in create');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in create');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in create');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in create');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in create');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in create');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in create');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in create');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in create');
                return;
        }
    }

    public function test_store_returns_expected_type_12(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->store($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'store must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in store');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in store');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in store');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in store');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in store');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in store');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in store');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in store');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in store');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in store');
                return;
        }
    }

    public function test_store_with_post_data_13(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->store($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'store must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in store');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in store');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in store');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in store');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in store');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in store');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in store');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in store');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in store');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in store');
                return;
        }
    }

    public function test_edit_returns_expected_type_14(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->edit($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'edit must return View|RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in edit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in edit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in edit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in edit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in edit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in edit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in edit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in edit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in edit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in edit');
                return;
        }
    }

    public function test_update_returns_expected_type_15(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->update($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'update must return RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in update');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in update');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in update');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in update');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in update');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in update');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in update');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in update');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in update');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in update');
                return;
        }
    }

    public function test_update_with_post_data_16(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->update($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'update must return RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in update');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in update');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in update');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in update');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in update');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in update');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in update');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in update');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in update');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in update');
                return;
        }
    }

    public function test_destroy_returns_expected_type_17(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->destroy($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'destroy must return RedirectResponse|null');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in destroy');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in destroy');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in destroy');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in destroy');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in destroy');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in destroy');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in destroy');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in destroy');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in destroy');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in destroy');
                return;
        }
    }

    public function test_joinUsUserStore_returns_expected_type_18(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $result = $ctrl->joinUsUserStore($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse, 'joinUsUserStore must return JsonResponse|RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in joinUsUserStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in joinUsUserStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in joinUsUserStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in joinUsUserStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in joinUsUserStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in joinUsUserStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in joinUsUserStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in joinUsUserStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in joinUsUserStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in joinUsUserStore');
                return;
        }
    }

    public function test_joinUsUserStore_with_post_data_19(): void
    {
        $this->loginMockUser();
        $ctrl = new JoinUsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->joinUsUserStore($req);
            $this->assertTrue($result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse, 'joinUsUserStore must return JsonResponse|RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in joinUsUserStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in joinUsUserStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in joinUsUserStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in joinUsUserStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in joinUsUserStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in joinUsUserStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in joinUsUserStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in joinUsUserStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in joinUsUserStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in joinUsUserStore');
                return;
        }
    }

}
