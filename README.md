jdeserialize
============

*jdeserialize* is a PHP library that aims to provide functions for reading serialized Java objects. The goal is to provide a representation of the serialized Java objects in an associative array format.

The library is heavily inspired (and most code bits copied) from the [jdeserialize](https://code.google.com/archive/p/jdeserialize/) library written in Java. The serialized samples in `samples/borrowed` are borrowed from [python-javaobj](https://github.com/tcalmant/python-javaobj).

## Features
1. Java objects unmarshalling to associative arrays
2. Dumping class files (not an accurate representation)

## To be developed
1. The following type codes are not yet implemented
    * `TC_EXCEPTION`
    * `TC_LONGSTRING`
    * `TC_RESET`
2. Class connections (modifiers, static/inner)
3. Better datatype conversions

## Usage
```php
$bin = file_get_contents("file_containing_serialized_object.bin");
$jd = new dehydr8\Jdeserialize\Deserializer($bin);

// the objects variable will contain all the instances
$objects = $jd->deserialize();

// normalization is used to convert those instances to associative arrays
$normalized = dehydr8\Jdeserialize\utils\Normalizer::normalizeObjects($objects);

// the class descriptions can be retrieved after deserialization
$classes = $jd->getClasses();

// the class descriptions can be printed like so
foreach ($classes as $description) {
  dehydr8\Jdeserialize\utils\ClassPrinter::print($description);
}
```
## Detailed Example
We will use the following POJO for this example:

```java
package com.codinghazard.serialization.model;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

public class Person implements Serializable {
    
    public static enum Gender {
        MALE, FEMALE
    }
    
    private static final long serialVersionUID = -8097012038146870908L;
    private final String name;
    private final int age;
    private final Gender gender;
    
    private List<Person> children = new ArrayList<Person>();

    public Person(String name, int age, Gender gender) {
        super();
        this.name = name;
        this.age = age;
        this.gender = gender;
    }
    
    public void addChild(Person child) {
        this.children.add(child);
    }

    // ...
}
```

Instantiating objects for the said POJO and writing them to the stream
```java
Person khalid = new Person("Khalid", 65, Person.Gender.MALE);
khalid.addChild(new Person("Osama", 28, Person.Gender.MALE));
khalid.addChild(new Person("Abdullah", 30, Person.Gender.MALE));

Person azfar = new Person("Azfar", 50, Person.Gender.MALE);
Person haris = new Person("Haris", 25, Person.Gender.FEMALE);
haris.setChildren(null);

azfar.addChild(haris);

List<Person> object = new ArrayList<Person>();
object.add(khalid);
object.add(azfar);

// ...

stream.writeObject(object);
```
will result in the following bytes
```
00000000 AC ED 00 05 73 72 00 13 6A 61 76 61 2E 75 74 69 ....sr..java.uti
00000010 6C 2E 41 72 72 61 79 4C 69 73 74 78 81 D2 1D 99 l.ArrayListx....
00000020 C7 61 9D 03 00 01 49 00 04 73 69 7A 65 78 70 00 .a....I..sizexp.
00000030 00 00 02 77 04 00 00 00 02 73 72 00 2B 63 6F 6D ...w.....sr.+com
00000040 2E 63 6F 64 69 6E 67 68 61 7A 61 72 64 2E 73 65 .codinghazard.se
00000050 72 69 61 6C 69 7A 61 74 69 6F 6E 2E 6D 6F 64 65 rialization.mode
00000060 6C 2E 50 65 72 73 6F 6E 8F A1 A2 73 7C 31 B1 84 l.Person...s|1..
00000070 02 00 04 49 00 03 61 67 65 4C 00 08 63 68 69 6C ...I..ageL..chil
00000080 64 72 65 6E 74 00 10 4C 6A 61 76 61 2F 75 74 69 drent..Ljava/uti
00000090 6C 2F 4C 69 73 74 3B 4C 00 06 67 65 6E 64 65 72 l/List;L..gender
000000A0 74 00 34 4C 63 6F 6D 2F 63 6F 64 69 6E 67 68 61 t.4Lcom/codingha
000000B0 7A 61 72 64 2F 73 65 72 69 61 6C 69 7A 61 74 69 zard/serializati
000000C0 6F 6E 2F 6D 6F 64 65 6C 2F 50 65 72 73 6F 6E 24 on/model/Person$
000000D0 47 65 6E 64 65 72 3B 4C 00 04 6E 61 6D 65 74 00 Gender;L..namet.
000000E0 12 4C 6A 61 76 61 2F 6C 61 6E 67 2F 53 74 72 69 .Ljava/lang/Stri
000000F0 6E 67 3B 78 70 00 00 00 41 73 71 00 7E 00 00 00 ng;xp...Asq.~...
00000100 00 00 02 77 04 00 00 00 02 73 71 00 7E 00 02 00 ...w.....sq.~...
00000110 00 00 1C 73 71 00 7E 00 00 00 00 00 00 77 04 00 ...sq.~......w..
00000120 00 00 00 78 7E 72 00 32 63 6F 6D 2E 63 6F 64 69 ...x~r.2com.codi
00000130 6E 67 68 61 7A 61 72 64 2E 73 65 72 69 61 6C 69 nghazard.seriali
00000140 7A 61 74 69 6F 6E 2E 6D 6F 64 65 6C 2E 50 65 72 zation.model.Per
00000150 73 6F 6E 24 47 65 6E 64 65 72 00 00 00 00 00 00 son$Gender......
00000160 00 00 12 00 00 78 72 00 0E 6A 61 76 61 2E 6C 61 .....xr..java.la
00000170 6E 67 2E 45 6E 75 6D 00 00 00 00 00 00 00 00 12 ng.Enum.........
00000180 00 00 78 70 74 00 04 4D 41 4C 45 74 00 05 4F 73 ..xpt..MALEt..Os
00000190 61 6D 61 73 71 00 7E 00 02 00 00 00 1E 73 71 00 amasq.~......sq.
000001A0 7E 00 00 00 00 00 00 77 04 00 00 00 00 78 71 00 ~......w.....xq.
000001B0 7E 00 0C 74 00 08 41 62 64 75 6C 6C 61 68 78 71 ~..t..Abdullahxq
000001C0 00 7E 00 0C 74 00 06 4B 68 61 6C 69 64 73 71 00 .~..t..Khalidsq.
000001D0 7E 00 02 00 00 00 32 73 71 00 7E 00 00 00 00 00 ~.....2sq.~.....
000001E0 01 77 04 00 00 00 01 73 71 00 7E 00 02 00 00 00 .w.....sq.~.....
000001F0 19 70 7E 71 00 7E 00 0A 74 00 06 46 45 4D 41 4C .p~q.~..t..FEMAL
00000200 45 74 00 05 48 61 72 69 73 78 71 00 7E 00 0C 74 Et..Harisxq.~..t
00000210 00 05 41 7A 66 61 72 78                         ..Azfarx
```

A JSON representation of the `object`
```json
[
  {
    "name": "Khalid",
    "age": 65,
    "gender": "MALE",
    "children": [
      {
        "name": "Osama",
        "age": 28,
        "gender": "MALE",
        "children": [
          
        ]
      },
      {
        "name": "Abdullah",
        "age": 30,
        "gender": "MALE",
        "children": [
          
        ]
      }
    ]
  },
  {
    "name": "Azfar",
    "age": 50,
    "gender": "MALE",
    "children": [
      {
        "name": "Haris",
        "age": 25,
        "gender": "FEMALE"
      }
    ]
  }
]
```
After dumping the class descriptions using the serialized object, we get:
```java
// 7e0000
class java.util.ArrayList implements java.io.Serializable {
    private static final long serialVersionUID = 0x7881d21d99c7619dL;

    int size;
}

// 7e0002
class com.codinghazard.serialization.model.Person implements java.io.Serializable {
    private static final long serialVersionUID = 0x8fa1a2737c31b184L;

    int age;
    java.util.List children;
    com.codinghazard.serialization.model.Person$Gender gender;
    java.lang.String name;
}

// 7e000b
enum java.lang.Enum {

}

// 7e000a
enum com.codinghazard.serialization.model.Person$Gender {
    MALE, FEMALE
}
```
and the normalized form (the result is deliberately JSON encoded for better readibility)
```json
[
  {
    "age": 65,
    "children": [
      {
        "age": 28,
        "children": [
          
        ],
        "gender": "MALE",
        "name": "Osama"
      },
      {
        "age": 30,
        "children": [
          
        ],
        "gender": "MALE",
        "name": "Abdullah"
      }
    ],
    "gender": "MALE",
    "name": "Khalid"
  },
  {
    "age": 50,
    "children": [
      {
        "age": 25,
        "children": null,
        "gender": "FEMALE",
        "name": "Haris"
      }
    ],
    "gender": "MALE",
    "name": "Azfar"
  }
]
```