<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Language};
use Illuminate\Support\Facades\Hash;

try {
	// Get pt-br language
	$lang = Language::where('code', 'pt-br')->first();

	if (!$lang) {
		echo "ERROR: pt-br language not found!\n";
		exit(1);
	}

	// Check if user already exists
	$existing = User::where('email', 'suporte@brandnewideascompany.com')->first();
	if ($existing) {
		echo "Super Admin already exists: {$existing->email}\n";
		echo "Updating password...\n";
		$existing->password = Hash::make('Admin@BrandNewIdeasCompany2026!');
		$existing->save();
		echo "Password updated successfully!\n";
		exit(0);
	}

	// Create Super Admin user
	$user = User::create([
		'id' => 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7',
		'name' => 'Super Admin',
		'email' => 'suporte@brandnewideascompany.com',
		'password' => Hash::make('Admin@BrandNewIdeasCompany2026!'),
		'type' => 'super admin',
		'lang' => $lang->code,
		'mode' => 'light',
		'email_verified_at' => now(),
	]);

	echo "✓ Super Admin created successfully!\n";
	echo "  Email: {$user->email}\n";
	echo "  Password: Admin@BrandNewIdeasCompany2026!\n";
	echo "  Type: {$user->type}\n";
} catch (Exception $e) {
	echo "ERROR: {$e->getMessage()}\n";
	echo $e->getTraceAsString();
	exit(1);
}
