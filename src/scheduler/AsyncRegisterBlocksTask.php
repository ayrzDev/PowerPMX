<?php

declare(strict_types=1);

namespace pocketmine\scheduler;

use pmmp\thread\ThreadSafeArray;
use pocketmine\block\Block;
use pocketmine\block\CustomBlockFactory;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\scheduler\AsyncTask;

final class AsyncRegisterBlocksTask extends AsyncTask
{

    private ThreadSafeArray $blockFuncs;
    private ThreadSafeArray $serializer;
    private ThreadSafeArray $deserializer;

    /**
     * @param Closure[] $blockFuncs
     * @phpstan-param array<string, array{(Closure(int): Block), (Closure(BlockStateWriter): Block), (Closure(Block): BlockStateReader)}> $blockFuncs
     */
    public function __construct(private string $cachePath, array $blockFuncs)
    {
        $this->blockFuncs = new ThreadSafeArray();
        $this->serializer = new ThreadSafeArray();
        $this->deserializer = new ThreadSafeArray();

        foreach ($blockFuncs as $identifier => [$blockFunc, $serializer, $deserializer]) {
            $this->blockFuncs[$identifier] = $blockFunc;
            $this->serializer[$identifier] = $serializer;
            $this->deserializer[$identifier] = $deserializer;
        }
    }

    public function onRun(): void
    {
        foreach ($this->blockFuncs as $identifier => $blockFunc) {
            CustomBlockFactory::getInstance()->registerBlock($blockFunc, $identifier, serializer: $this->serializer[$identifier], deserializer: $this->deserializer[$identifier]);
        }
    }
}
