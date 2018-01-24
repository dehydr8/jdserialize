<?php namespace dehydr8\Jdeserialize\constants;

abstract class Constants {
  const STREAM_MAGIC        = 0xaced;
  const STREAM_VERSION      = 0x05;
  
  const TC_BASE             = 0x70;
  const TC_NULL             = 0x70;
  const TC_REFERENCE        = 0x71;
  const TC_CLASSDESC        = 0x72;
  const TC_OBJECT           = 0x73;
  const TC_STRING           = 0x74;
  const TC_ARRAY            = 0x75;
  const TC_CLASS            = 0x76;
  const TC_BLOCKDATA        = 0x77;
  const TC_ENDBLOCKDATA     = 0x78;
  const TC_RESET            = 0x79;
  const TC_BLOCKDATALONG    = 0x7A;
  const TC_EXCEPTION        = 0x7B;
  const TC_LONGSTRING       = 0x7C;
  const TC_PROXYCLASSDESC   = 0x7D;
  const TC_ENUM             = 0x7E;
  const TC_MAX              = 0x7E;

  const BASE_WIRE_HANDLE    = 0x7e0000;

  const SC_WRITE_METHOD     = 0x01;
  const SC_BLOCK_DATA       = 0x08;
  const SC_SERIALIZABLE     = 0x02;
  const SC_EXTERNALIZABLE   = 0x04;
  const SC_ENUM             = 0x10;
}

?>