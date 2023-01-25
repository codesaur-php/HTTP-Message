<?php

namespace codesaur\Http\Message;

class OutputBuffer
{
    public function start(int $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        ob_start(null, $chunk_size, $flags);
    }
    
    public function startCallback(callable $output_callback, $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        ob_start($output_callback, $chunk_size, $flags);
    }
    
    public function startCompress()
    {
        $this->startCallback([$this, 'compress']);
    }
    
    public function flush()
    {
        if (ob_get_level()) {
            ob_flush();
        }
    }
    
    public function endClean()
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
    }
    
    public function endFlush()
    {
        if (ob_get_level()) {
            ob_end_flush();
        }
    }
    
    public function getLength(): int|false
    {
        return ob_get_length();
    }

    public function getContents(): string|null|false
    {
        if (!ob_get_level()) {
            return null;
        }
        
        return ob_get_contents();
    }
    
    public function compress($buffer)
    {
        $search = [
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s',     // shorten multiple whitespace sequences
        ];
        return preg_replace($search, ['>', '<', '\\1'], $buffer);
    }
}
