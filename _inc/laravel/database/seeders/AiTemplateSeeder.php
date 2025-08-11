<?php

namespace Database\Seeders;

use App\Config\Constants\SeedersTemplating;
use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\{Facades\Log, Str};
use Symfony\Component\Console\Output\ConsoleOutput;

class AiTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $output = new ConsoleOutput();
        $output->writeln('');
        $output->writeln('<question>------------- ### AI Templates Seeding ### ------------- </question>');
        $output->writeln('<info> Seeding Ai Templates with ' . static::class . '</info>');
        $now = now()->toDateTimeString();
        $uuids = [];
        $templates = SeedersTemplating::aiDefaultTemplate();
        $templates = array_map(function (array $r) use ($now, &$uuids) {
            $templateId = '';
            $acc = 0;
            do {
                if ($acc > 100_000) {
                    Log::warning(SeedersTemplating::SCAPE_MSG);
                    break;
                }
                $templateId = (string) Str::uuid();
                $acc += 1;
            } while (in_array($templateId, $uuids, true));
            $uuids[] = $templateId;
            return array_merge($r, [
                'id' => $templateId,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }, $templates);
        foreach ($templates as $t) Template::create($t);
        $output->writeln('<info>                                Done creating AI templates!</info>');
        $output->writeln('<question>----------- Ending of Ai Templates Seeding --------</question>');
        $output->writeln('');
    }
}
