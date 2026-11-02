import { Client, General } from 'package-name';

// Nullable request params accept null under strict mode, in both call styles.
const general = new General(new Client());
void general.nullable({ required: 'required', nullable: null, optionalNullable: null, nullableInteger: null });
void general.nullable('required', null, null, undefined, null);
