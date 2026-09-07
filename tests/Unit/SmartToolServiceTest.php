<?php

namespace Tests\Unit;

use App\Services\Studio\SmartToolService;
use PHPUnit\Framework\TestCase;

class SmartToolServiceTest extends TestCase
{
    public function test_scene_builder_returns_numbered_timed_scenes(): void
    {
        $result = (new SmartToolService())->build('scenes', 'First scene. Second scene.');
        $this->assertSame('studio-scenes-v1', $result['format']);
        $this->assertCount(2, $result['scenes']);
        $this->assertSame(1, $result['scenes'][0]['number']);
    }

    public function test_caption_builder_produces_monotonic_timestamps(): void
    {
        $captions = (new SmartToolService())->build('captions', 'Hello world. Welcome to the studio.')['captions'];
        $this->assertGreaterThan($captions[0]['start'], $captions[0]['end']);
        $this->assertSame($captions[0]['end'], $captions[1]['start']);
    }
}
