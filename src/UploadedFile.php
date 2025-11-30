<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 UploadedFileInterface хэрэгжилт.
 *
 * Энэхүү класс нь upload хийгдсэн файлын metadata болон
 * түр хадгалагдсан файлын зам (tmp_name)-ийг удирдах зориулалттай.
 *
 * Онцлог:
 *  - Клиентээс илгээсэн filename, media type, size, error-с мэдээлэл агуулна
 *  - moveTo() нь файлыг серверийн түр хавтасаас зорилтот байршил руу зөөж өгнө
 *  - Файл нэг удаа л зөөгдөх боломжтой (PSR-7 шаардлага)
 *  - CLI болон вэб сервер орчинд тусдаа file-move логик ашиглана
 */
class UploadedFile implements UploadedFileInterface, \JsonSerializable
{
    /**
     * Клиентээс ирсэн filename (original name)
     *
     * @var string|null
     */
    protected ?string $name;
    
    /**
     * Клиентээс ирсэн MIME type (жишээ: image/png)
     *
     * @var string|null
     */
    protected ?string $type;
    
    /**
     * Файлын хэмжээ (bytes)
     *
     * @var int|null
     */
    protected ?int $size;
    
    /**
     * PHP upload error код
     *
     * @var int
     */
    protected int $error;
    
    /**
     * PHP-ийн upload түр хавтас дахь файлын зам
     *
     * @var string
     */
    protected string $tmp_name;
    
    /**
     * Файл аль хэдийн зөөгдсөн эсэхийг илэрхийлэх flag.
     *
     * @var bool
     */
    private bool $_moved = false;
    
    /**
     * Upload хийгдсэн файлын metadata-г инициализац хийх.
     *
     * @param string      $tmp_name  Түр хадгалагдсан файл (tmp path)
     * @param string|null $name      Клиент filename
     * @param string|null $type      MIME type
     * @param int|null    $size      Файлын хэмжээ
     * @param int         $error     PHP upload error код
     */
    public function __construct(string $tmp_name, ?string $name, ?string $type, ?int $size, int $error)
    {
        $this->tmp_name = $tmp_name;
        
        $this->name  = $name;
        $this->type  = $type;
        $this->size  = $size;
        $this->error = $error;
    }
    
    /**
     * Клиентээс ирсэн эх filename-г буцаана.
     *
     * @return string|null Original client filename
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * Клиентээс ирсэн MIME төрөл (жишээ: image/jpeg)
     *
     * @return string|null MIME төрөл
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Upload хийгдсэн файлын хэмжээ (байвал)
     *
     * @return int|null Файлын хэмжээ bytes-ээр
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * PHP upload error кодыг буцаана.
     *
     * @return int PHP UPLOAD_ERR_* тогтмолын аль нэг
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Файлаас stream үүсгэх хэрэгжилт.  
     * support хийгдээгүй тул exception шиднэ.
     *
     * PSR-7 дагуу stream дэмжих боломжтой ч
     * codesaur implementation-д хэрэггүй тул stub хэлбэртэй.
     *
     * @return StreamInterface
     *
     * @throws \RuntimeException
     */
    public function getStream(): StreamInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Upload хийгдсэн файлыг түр хавтасаас зорилтот байршил руу зөөнө.
     *
     * PSR-7 шаардлага:
     *  - Файлыг нэг л удаа зөөх ёстой (дараа нь дахин move хийхийг хориглоно)
     *  - Error code != UPLOAD_ERR_OK бол exception
     *  - targetPath заавал хоосон биш байх ёстой
     *  - CLI болон веб сервер орчинд өөр file-moving механизм ашиглана
     *
     * @param string $targetPath Зорилтот файлын абсолют эсвэл харьцангуй зам
     *
     * @return void
     *
     * @throws \InvalidArgumentException targetPath хоосон бол
     * @throws \RuntimeException Файл байхгүй эсвэл аль хэдийн зөөгдсөн бол
     * @throws \RuntimeException Upload error гарсан бол
     * @throws \RuntimeException Файлыг зөөх эсвэл temp файлыг устгахад алдаа гарвал
     */
    public function moveTo(string $targetPath): void
    {
        if (empty($targetPath)) {
            throw new \InvalidArgumentException('Invalid target path!');
        }

        if (empty($this->tmp_name)) {
            throw new \InvalidArgumentException('Upload file path not found!');
        }
        
        if ($this->_moved) {
            throw new \RuntimeException(sprintf('Uploaded file already moved from %s!', $this->tmp_name));
        }
                
        // Upload error шалгах
        switch ($this->error) {
            case \UPLOAD_ERR_OK:
                break;

            case \UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent!');

            case \UPLOAD_ERR_INI_SIZE:
            case \UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded filesize limit!');

            default:
                throw new \RuntimeException('Unknown errors on file upload!');
        }

        // CLI mode
        if (\PHP_SAPI == 'cli') {
            if (!\rename($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(
                    \sprintf('Error moving uploaded file %s to %s!', $this->tmp_name, $targetPath)
                );
            }
        } 
        // Web server mode
        else {
            if (!\copy($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(
                    \sprintf('Error moving uploaded file %s to %s!', $this->tmp_name, $targetPath)
                );
            }

            if (!\unlink($this->tmp_name)) {
                throw new \RuntimeException(
                    \sprintf('Error removing uploaded temp file %s!', $this->tmp_name)
                );
            }
        }
        
        $this->_moved = true;
    }

    /**
     * UploadedFile объектыг JSON руу serialize хийхэд ашиглагдах утгууд.
     *
     * @return mixed Объектын бүх property-г key/value хэлбэрээр буцаана
     */
    public function jsonSerialize(): mixed
    {
        return \get_object_vars($this);
    }
}
