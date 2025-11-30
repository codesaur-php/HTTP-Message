<?php

namespace codesaur\Http\Message;

/**
 * PHP output buffering-ийн wrapper класс.
 *
 * Энэ класс нь ob_start(), ob_flush(), ob_get_contents() зэрэг
 * PHP-ийн output buffer функцуудыг илүү цэгцтэй, объект хэлбэрээр
 * ашиглах боломж олгоно.
 *
 * Үндсэн хэрэглээ:
 *  - Output stream (Output.php) дотор хэрэглэгддэг
 *  - HTML output-ийг цэвэрлэх, шахах (minify) боломжтой
 *  - Callback ашиглан dynamic buffer удирдах
 *
 * Онцлог:
 *  - Buffer эхлүүлэх (with callback or without callback)
 *  - Flush / Clean / EndFlush зэрэг lifecycle функцийг нэг дор
 *  - Buffer-ийн урт болон доторх контентыг аюулгүй авах
 *  - whitespace compression (minification) дэмжинэ
 */
class OutputBuffer
{
    /**
     * Output buffering эхлүүлнэ.
     *
     * @param int $chunk_size  Buffer-ийн chunk хэмжээ (0 = буферлэх)
     * @param int $flags       Output handler flags (PHP default: PHP_OUTPUT_HANDLER_STDFLAGS)
     *
     * @return void
     */
    public function start(int $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        \ob_start(null, $chunk_size, $flags);
    }
    
    /**
     * Callback-тэй output buffering эхлүүлнэ.
     *
     * @param callable $output_callback  Buffer process хийх callback
     * @param int      $chunk_size
     * @param int      $flags
     *
     * @return void
     */
    public function startCallback(callable $output_callback, $chunk_size = 0, int $flags = \PHP_OUTPUT_HANDLER_STDFLAGS)
    {
        \ob_start($output_callback, $chunk_size, $flags);
    }
    
    /**
     * Output buffering-ийг compress() функц ашиглан эхлүүлнэ.
     * HTML-ийг whitespace багасгасан, шахсан хэлбэрт шилжүүлдэг.
     *
     * @return void
     */
    public function startCompress()
    {
        $this->startCallback([$this, 'compress']);
    }
    
    /**
     * Буферийн одоогийн контентыг гаргаж (flush) буцаан хоосолно.
     *
     * @return void
     */
    public function flush()
    {
        if (\ob_get_level()) {
            \ob_flush();
        }
    }
    
    /**
     * Буферийн контентыг устгаж буферийг хаана.
     *
     * @return void
     */
    public function endClean()
    {
        if (\ob_get_level()) {
            \ob_end_clean();
        }
    }
    
    /**
     * Буферийг flush хийж чацруулан хаана.
     *
     * @return void
     */
    public function endFlush()
    {
        if (\ob_get_level()) {
            \ob_end_flush();
        }
    }
    
    /**
     * Буферийн уртыг буцаана.
     *
     * @return int|false Буферийн хэмжээ, эсвэл false
     */
    public function getLength(): int|false
    {
        return \ob_get_length();
    }

    /**
     * Буфер доторх контентыг буцаана.
     *
     * @return string|null|false
     *         - string: буферийн контент
     *         - null:   буфер байхгүй үед
     *         - false:  алдаа
     */
    public function getContents(): string|null|false
    {
        if (!\ob_get_level()) {
            return null;
        }
        
        return \ob_get_contents();
    }
    
    /**
     * HTML output-ийг шахах (whitespace compression).
     *
     * - Тагийн арын илүүдэл whitespace-ийг устгана
     * - Тагийн өмнөх whitespace-ийг багасгана
     * - Олон whitespace-ийг 1 болгож багасгана
     *
     * @param string $buffer  HTML buffer
     *
     * @return string Шахагдсан HTML
     */
    public function compress($buffer)
    {
        $search = [
            '/\>[^\S ]+/s', // Тагийн арын whitespace-үүдийг арилгана (space үлдээнэ)
            '/[^\S ]+\</s', // Тагийн өмнөх whitespace-үүдийг арилгана
            '/(\s)+/s',     // Олон whitespace-ийг 1 болгоно
        ];
        return \preg_replace($search, ['>', '<', '\\1'], $buffer);
    }
}
