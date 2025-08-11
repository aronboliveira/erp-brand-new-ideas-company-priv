<?php

namespace Tests\Unit\Models;

use App\Models\ProjectUser;
use Tests\TestCase;

class ProjectUserTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** The fillable array must expose
	 ** project_id, user_id, invited_by.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['project_id', 'user_id', 'invited_by'];

		$this->assertSame($expected, (new ProjectUser)->getFillable());
	}
}
