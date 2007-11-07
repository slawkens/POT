<?php

/**#@+
 * @version 0.0.6+SVN
 * @since 0.0.6+SVN
 */

/**
 * Code in this file bases on oryginal OTServ binary format loading C++ code (fileloader.h, fileloader.cpp).
 * 
 * @package POT
 * @author Wrzasq <wrzasq@gmail.com>
 * @copyright 2007 (C) by Wrzasq
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License, Version 3
 */

/**
 * Universal OTServ binary formats reader.
 * 
 * @package POT
 */
class OTS_FileLoader
{
/**
 * Start of node.
 */
    const NODE_START = 0xFE;
/**
 * End of node.
 */
    const NODE_END = 0xFF;
/**
 * Escape another special byte.
 */
    const ESCAPE_CHAR = 0xFD;

/**
 * File handle.
 * 
 * @var resource
 */
    private $file;

/**
 * Root node.
 * 
 * @var OTS_FileNode
 */
    protected $root;

/**
 * Cache handler.
 * 
 * @var IOTS_FileCache
 */
    private $cache;

/**
 * Sets cache handler.
 * 
 * @param IOTS_FileCache $cache Cache handler (leave this parameter if you want to unset caching).
 */
    public function setCacheDriver(IOTS_FileCache $cache = null)
    {
        $this->cache = $cache;
    }

/**
 * Opens file.
 * 
 * @param string $file Filepath.
 * @throws E_OTS_FileLoaderError When error occurs during file operation.
 */
    public function loadFile($file)
    {
        // attemts to read from cache
        if( isset($this->cache) )
        {
            $this->root = $this->cache->readCache( md5_file($file) );

            // checks if cache was loaded
            if( isset($this->root) )
            {
                return;
            }
        }

        // opens for read in binary mode
        $this->file = fopen($file, 'rb');

        if($this->file)
        {
            // reads file version
            $version = unpack('L', fread($this->file, 4) );

            if($version[0] > 0)
            {
                throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_INVALID_FILE_VERSION);
            }

            $this->safeSeek(4);

            if( $this->readByte() != self::NODE_START)
            {
                throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_INVALID_FORMAT);
            }

            // reads root node
            $this->root = new OTS_FileNode();
            $this->parseNode($this->root);

            fclose($this->file);

            // writes new cache
            if( isset($this->cache) )
            {
                $this->cache->writeCache( md5_file($file), $this->root);
            }
        }
        // failed to open the file
        else
        {
            throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_CAN_NOT_OPEN);
        }
    }

/**
 * Loades node from file.
 * 
 * @param OTS_FileNode $node Node into which file fragment should be parsed.
 * @throws E_OTS_FileLoaderError When error occurs during file operation.
 */
    private function parseNode(OTS_FileNode $node)
    {
        $current = $node;

        // reads node type
        $current->setType( $this->readByte() );

        $buffer = '';
        $propertiesSet = false;

        while(true)
        {
            // reads single byte from file
            $byte = $this->readByte();

            switch($byte)
            {
                // start of new node
                case self::NODE_START:
                    // reads child node
                    $child = new OTS_FileNode();
                    $current->setBuffer($buffer);
                    $propertiesSet = true;
                    $current->setChild($child);
                    $this->parseNode($child);
                    break;

                // end of current node
                case self::NODE_END:
                    if(!$propertiesSet)
                    {
                        $current->setBuffer($buffer);
                    }

                    // end of file
                    if($node === $this->root)
                    {
                        return;
                    }

                    switch( $this->readByte() )
                    {
                        // starts next node
                        case self::NODE_START:
                            $next = new OTS_FileNode();
                            $current->setNext($next);
                            $current = $next;
                            $current->setType( $this->readByte() );
                            $propertiesSet = false;
                            $buffer = '';
                            break;

                        // up 1 level and move 1 position back
                        case self::NODE_END:
                            $this->safeSeek( $this->safeTell() - 1);
                            return;
                            break;

                        // invliad file format
                        default:
                            throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_INVALID_FORMAT);
                    }

                    break;

                // ignores next character
                case self::ESCAPE_CHAR:
                    $buffer .= chr( $this->readByte() );
                    break;

                // normal character
                default:
                    $buffer .= chr($byte);
            }
        }
    }

/**
 * Reads single byte from file.
 * 
 * @return int Read value.
 * @throws E_OTS_FileLoaderError When error occurs during file operation.
 */
    private function readByte()
    {
        $value = fgetc($this->file);

        // checks eof
        if($value === false)
        {
            throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_EOF);
        }

        return ord($value);
    }

/**
 * Seeks file pointer into given position.
 * 
 * @param int $pos Seek position.
 * @throws E_OTS_FileLoaderError When error occurs during file operation.
 */
    private function safeSeek($pos)
    {
        if( fseek($this->file, $pos, SEEK_SET) == -1)
        {
            // error occured
            throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_SEEK_ERROR);
        }
    }

/**
 * Returns current position in file.
 * 
 * @return int Position in file.
 * @throws E_OTS_FileLoaderError When error occurs during file operation.
 */
    private function safeTell()
    {
        // reads file position
        $pos = ftell($this->file);

        // checks if operation succeeded
        if($pos === false)
        {
            throw new E_OTS_FileLoaderError(E_OTS_FileLoaderError::ERROR_TELL_ERROR);
        }

        return $pos;
    }
}

/**#@-*/

?>
