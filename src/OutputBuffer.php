<?php declare(strict_types=1);

namespace codesaur\Http\Message;

class OutputBuffer
{
    public function start($chunk_size = 0, $erase = PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        $this->startCallback(null, $chunk_size, $erase);
    }
    
    public function startCallback($output_callback, $chunk_size, $erase)
    {
        ob_start($output_callback, $chunk_size, $erase);
    }
    
    public function startCompress($chunk_size = 0, $erase = PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        $this->startCallback(array($this, 'compress'), $chunk_size, $erase);
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
    
    public function getLength(): int
    {
        return ob_get_length();
    }

    public function getContents(): ?string
    {
        if (!ob_get_level()) {
            return null;
        }
        
        return ob_get_contents();
    }
    
    public function compress($buffer)
    {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s',     // shorten multiple whitespace sequences
        );
        
        return preg_replace($search, array('>', '<', '\\1'), $buffer);
    }
}
