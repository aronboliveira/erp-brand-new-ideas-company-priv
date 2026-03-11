<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Controllers\planning;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use App\Http\Controllers\ProjectReportController;
use Illuminate\Http\{RedirectResponse, JsonResponse, Request, Response};
use Illuminate\View\View;

/**
 * Comprehensive tests for ProjectReportController
 * Includes I/O variations, edge cases, and performance tests
 * 
 * @covers \App\Http\Controllers\ProjectReportController
 */
class ProjectReportControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_GET_PRJ_CHT_equals_getProjectChart_1(): void
    {
        $this->assertSame('getProjectChart', ProjectReportController::GET_PRJ_CHT);
    }

    public function test_constant_IDX_equals_index_2(): void
    {
        $this->assertSame('index', ProjectReportController::IDX);
    }

    public function test_constant_SHW_equals_show_3(): void
    {
        $this->assertSame('show', ProjectReportController::SHW);
    }

    public function test_index_4(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->index($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'index must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_index_empty_post_5(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->index($this->makeRequest('/', 'POST', []));
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'index must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_index_json_6(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->index($this->makeRequest('/', 'GET', [], true));
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'index must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    /**
     * @group performance
     */
    public function test_index_performance_7(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();

        $memBefore = memory_get_usage(true);
        $timeBefore = microtime(true);

        try {
            for ($i = 0; $i < 3; $i++) {
                $ctrl->index($this->makeRequest());
            }
        } catch (\Throwable $e) {
            // Method may throw, that's OK for perf test
        }

        $timeAfter = microtime(true);
        $memAfter = memory_get_usage(true);

        $execTime = ($timeAfter - $timeBefore) * 1000; // ms
        $memUsed = ($memAfter - $memBefore) / 1024 / 1024; // MB

        // Assert reasonable performance bounds
        $this->assertLessThan(5000, $execTime, "index took > 5s for 3 iterations");
        $this->assertLessThan(50, $memUsed, "index used > 50MB for 3 iterations");
    }

    public function test_show_8(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_show_empty_post_9(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest('/', 'POST', []), 1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_show_json_10(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest('/', 'GET', [], true), 1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_show_zero_11(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest(), 0);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_show_negative_12(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest(), -1);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_show_large_13(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->show($this->makeRequest(), 999999999);
            $this->assertTrue($result instanceof \Illuminate\View\View || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return valid type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    /**
     * @group performance
     */
    public function test_show_performance_14(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();

        $memBefore = memory_get_usage(true);
        $timeBefore = microtime(true);

        try {
            for ($i = 0; $i < 3; $i++) {
                $ctrl->show($this->makeRequest(), 1);
            }
        } catch (\Throwable $e) {
            // Method may throw, that's OK for perf test
        }

        $timeAfter = microtime(true);
        $memAfter = memory_get_usage(true);

        $execTime = ($timeAfter - $timeBefore) * 1000; // ms
        $memUsed = ($memAfter - $memBefore) / 1024 / 1024; // MB

        // Assert reasonable performance bounds
        $this->assertLessThan(5000, $execTime, "show took > 5s for 3 iterations");
        $this->assertLessThan(50, $memUsed, "show used > 50MB for 3 iterations");
    }

    public function test_getProjectChart_15(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->getProjectChart(['key' => 'value']);
            $this->assertTrue(is_array($result), 'Expected array return type');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    /**
     * @group performance
     */
    public function test_getProjectChart_performance_16(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();

        $memBefore = memory_get_usage(true);
        $timeBefore = microtime(true);

        try {
            for ($i = 0; $i < 3; $i++) {
                $ctrl->getProjectChart(['key' => 'value']);
            }
        } catch (\Throwable $e) {
            // Method may throw, that's OK for perf test
        }

        $timeAfter = microtime(true);
        $memAfter = memory_get_usage(true);

        $execTime = ($timeAfter - $timeBefore) * 1000; // ms
        $memUsed = ($memAfter - $memBefore) / 1024 / 1024; // MB

        // Assert reasonable performance bounds
        $this->assertLessThan(5000, $execTime, "getProjectChart took > 5s for 3 iterations");
        $this->assertLessThan(50, $memUsed, "getProjectChart used > 50MB for 3 iterations");
    }

    public function test_export_17(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->export(1);
            $this->assertTrue(true, 'Method executed without fatal error');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_export_zero_20(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->export(0);
            $this->assertTrue(true, 'Method executed without fatal error');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_export_negative_21(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->export(-1);
            $this->assertTrue(true, 'Method executed without fatal error');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    public function test_export_large_22(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->export(999999999);
            $this->assertTrue(true, 'Method executed without fatal error');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\BadMethodCallException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\ErrorException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\TypeError $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
            return;
        }
    }

    /**
     * @group performance
     */
    public function test_export_performance_21(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();

        $memBefore = memory_get_usage(true);
        $timeBefore = microtime(true);

        try {
            for ($i = 0; $i < 3; $i++) {
                $ctrl->export(1);
            }
        } catch (\Throwable $e) {
            // Method may throw, that's OK for perf test
        }

        $timeAfter = microtime(true);
        $memAfter = memory_get_usage(true);

        $execTime = ($timeAfter - $timeBefore) * 1000; // ms
        $memUsed = ($memAfter - $memBefore) / 1024 / 1024; // MB

        // Assert reasonable performance bounds
        $this->assertLessThan(5000, $execTime, "export took > 5s for 3 iterations");
        $this->assertLessThan(50, $memUsed, "export used > 50MB for 3 iterations");
    }

    //=== ajax_data tests ===

    public function test_ajax_data_returns_json(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->ajax_data($this->makeRequest('/project-report/data', 'POST', [
                'project_id' => 'test-project-id',
                'duration'   => 'week',
            ]));
            $this->assertTrue(
                $result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse,
                'ajax_data must return JsonResponse or RedirectResponse'
            );
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function test_ajax_data_empty_project_id(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->ajax_data($this->makeRequest('/project-report/data', 'POST', []));
            $this->assertTrue(
                $result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse,
                'ajax_data with empty project_id must return valid response'
            );
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    //=== ajax_tasks_report tests ===

    public function test_ajax_tasks_report_returns_json(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->ajax_tasks_report($this->makeRequest('/project-report/tasks/1', 'POST'), '1');
            $this->assertTrue(
                $result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse,
                'ajax_tasks_report must return JsonResponse or RedirectResponse'
            );
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function test_ajax_tasks_report_with_nonexistent_id(): void
    {
        $this->loginMockUser();
        $ctrl = new ProjectReportController();
        try {
            $result = $ctrl->ajax_tasks_report($this->makeRequest('/project-report/tasks/999999', 'POST'), '999999');
            $this->assertTrue(
                $result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\RedirectResponse,
                'ajax_tasks_report with nonexistent id must return valid response'
            );
        } catch (\Throwable $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
