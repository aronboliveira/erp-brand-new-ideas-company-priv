<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\ContractController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class ContractControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_contracts_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.index');
	}

	public function test_contracts_create_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.create');
	}

	public function test_contracts_show_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.show');
	}

	public function test_contracts_edit_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.edit');
	}

	public function test_contracts_description_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.description');
	}

	public function test_contracts_grid_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.grid');
	}

	public function test_contracts_preview_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.preview');
	}

	public function test_contracts_copy_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.copy');
	}

	public function test_contracts_signature_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.signature');
	}

	public function test_contracts_template_view_file_exists(): void
	{
		$this->assertBladeViewExists('contracts.template');
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_contract_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(ContractController::class),
			'ContractController class should be loadable.'
		);
	}

	public function test_contract_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(ContractController::class);

		$expected = [
			'index',
			'create',
			'store',
			'show',
			'edit',
			'update',
			'destroy',
			'description',
			'grid',
			'fileUpload',
			'fileDownload',
			'fileDelete',
			'contractStatusEdit',
			'contractDescriptionStore',
			'printContract',
			'copyContract',
			'copyContractStore',
			'sendMailContract',
			'pdfFromContract',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"ContractController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_contract_controller_constants_defined(): void
	{
		$this->assertSame('index', ContractController::IDX);
		$this->assertSame('create', ContractController::CRT);
		$this->assertSame('store', ContractController::STR);
		$this->assertSame('show', ContractController::SHW);
		$this->assertSame('edit', ContractController::EDT);
		$this->assertSame('update', ContractController::UPD);
		$this->assertSame('destroy', ContractController::DEL);
		$this->assertSame('description', ContractController::DSCP);
		$this->assertSame('grid', ContractController::GRD);
		$this->assertSame('fileUpload', ContractController::F_UPL);
		$this->assertSame('fileDownload', ContractController::F_DWN);
		$this->assertSame('fileDelete', ContractController::F_DEL);
		$this->assertSame('contractStatusEdit', ContractController::CTC_ST_EDT);
		$this->assertSame('contractDescriptionStore', ContractController::CTC_DSCP_STR);
		$this->assertSame('printContract', ContractController::PRNT_CTC);
		$this->assertSame('copyContract', ContractController::CPY_CTC);
		$this->assertSame('copyContractStore', ContractController::CPY_CTC_STR);
		$this->assertSame('sendMailContract', ContractController::SND_ML_CTC);
		$this->assertSame('pdfFromContract', ContractController::PDF_FRM_CTC);
	}
}
