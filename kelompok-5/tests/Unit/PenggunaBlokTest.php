<?php

namespace Tests\Unit;

use App\Models\Pengguna;
use PHPUnit\Framework\TestCase;

class PenggunaBlokTest extends TestCase
{
    public function test_it_accepts_block_prefixes_for_the_selected_gang(): void
    {
        $this->assertTrue(Pengguna::blokSesuaiGang('Gang C16/E1', 'C16/04'));
        $this->assertTrue(Pengguna::blokSesuaiGang('Gang C16/E1', 'E1/22'));
        $this->assertTrue(Pengguna::blokSesuaiGang('Gang E6', 'E6/22'));
    }

    public function test_block_is_required(): void
    {
        $this->assertFalse(Pengguna::blokSesuaiGang('Gang E6', null));
        $this->assertFalse(Pengguna::blokSesuaiGang('Gang E6', ''));
    }

    public function test_it_rejects_a_block_prefix_from_another_gang(): void
    {
        $this->assertFalse(Pengguna::blokSesuaiGang('Gang C16/E1', 'E4/01'));
        $this->assertFalse(Pengguna::blokSesuaiGang('Gang E6', 'E/22'));
    }
}
