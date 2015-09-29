BAM
---
BAM is an application that builds the base of your applications for you.
In your .bam file you can specify what fields your models have (including
foreign keys), and basic ACL and field-visibility rules. BAM takes the .bam
file and can use Processors to convert it into a database schema (via
[DBSteward](https://github.com/nkiraly/DBSteward)), Database Models (for
[Kohana](https://github.com/kohana/kohana)), a basic Admin/Frontend (
using [Kohana](https://github.com/kohana/kohana)), or a basic ER diagram (
via a [.dot](https://en.wikipedia.org/wiki/DOT_%28graph_description_language%29)
file).  Other frameworks and languages can be supported by creating new 
processors for them.

Fields in BAM can have one of a set number of types and paramters. These
types and their parameters should be supported as well as possible by the
processor.

Processors may depend on other plugins, for instance if the destination 
parameter for a file is an S3 bucket. Until more experince is gained with
how this works best, the interop is left unspecified.

Types
-----

| BAM Type    | Postgres Type | HTML5 Type |
| ----------- | ------------- | ---------- |
| boolean     | boolean       | checkbox    |
| bytea       | bytea         | textarea (hex-encoded) |
| cidr        | cidr          | text (pattern) |
| date        | date          | date       |
| email       | text (constraint) | email  |
| enum        | enum          | select     |
| file        | text          | file       |
| float       | real          | number     |
| inet        | inet          | text (pattern) |
| integer     | integer       | number (step=1) |
| json        | json          | textarea   |
| macaddr     | macaddr       | text (pattern) |
| serial      | serial        | n/a        |
| string      | text          | text       |
| tel         | text (constraint) | tel (pattern) |
| text        | text          | textarea   |
| time        | time          | time       |
| timestamptz | timestamptz   | datetime   |
| url         | url           | text (constraint) |
| uuid        | uuid          | text (constraint) |

| BAM Type    | BAM parameters |
| ----------- | -------------- |
| boolean     | |
| bytea       | minlength, maxlength |
| cidr        | |
| date        | min, max |
| email       | |
| enum        | |
| file        | accept, dest |
| float       | min, max, step |
| inet        | |
| integer     | min, max, step |
| json        | |
| macaddr     | |
| serial      | |
| string      | minlength, maxlength, pattern |
| tel         | pattern |
| text        | minlength, maxlength, pattern |
| time        | min, max |
| timestamptz | min, max, default_cts, update_cts |
| url         | |
| uuid        | default_v4, default_v1|
