<?php

namespace App\Traits;

trait LogsIcons
{
	private const ICON_GROUPS = [
		['k' => 'Add', 'c' => [['Contact', 'notebook'], ['Product', 'shopping-cart-plus']]],
		['k' => 'Create', ['c' => [
			['Bug', 'bug'],
			['Deal Call', 'phone-plus'],
			['Deal Email', 'record-mail'],
			['Expense', 'clipboard-list'],
			['Invoice', 'file-plus'],
			['Milestone', 'crop'],
			['Task', 'list'],
			['User', 'user'],
		]]],
		['k' => 'Move', 'c' => [['', 'arrows-maximize'], ['Task', 'command']]],
		['k' => 'Update', ['c' => [['Sources', 'brand-open-source']]]],
		['k' => 'Upload', ['c' => [['File', 'cloud-upload']]]],
		['k' => 'User', 'c' => [
			['Assigned to the Task', 'user-check'],
			['Removed from the Task', 'user-x'],
		]],
	];
	private static ?array $iconMap = null;

	public function logIcon(): string
	{
		return self::getIconMap()[$this->log_type] ?? '';
	}

	private static function getIconMap(): array
	{
		if (!self::$iconMap) {
			$m = [];
			foreach (self::ICON_GROUPS as $g) {
				$p = $g['k'];
				foreach ($g['c'] as [$s, $code]) {
					$key = $p . ($s ? " $s" : '');
					$m[$key] = "ti-{$code}";
				}
			}
			self::$iconMap = $m;
		}
		return self::$iconMap;
	}
}
