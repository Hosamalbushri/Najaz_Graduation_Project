# Najaz GraphQL – Citizen Auth Notes

## Overview

We added citizen-facing GraphQL endpoints plus supporting infrastructure so Najaz's mobile/web apps can create accounts and manage sessions using the same JWT guard introduced for citizens (`citizen-api`). These changes mirror Bagisto's official Shop GraphQL API but operate on the Najaz citizen domain models.

## Key Additions

- **Helper**: `Najaz\GraphQLAPI\NajazGraphql` centralizes request validation and guard-aware authorization (status/is_verified checks) via the `citizen-api` guard. A global helper `najaz_graphql()` resolves the singleton.
- **Model override**: `Najaz\GraphQLAPI\Models\Citizen\Citizen` now implements `Authenticatable` + `JWTSubject` so jwt-auth can issue/verify tokens for citizens.
- **Translations**: Auth-specific messages for citizens (`najaz_graphql::app.citizens.*`) in Arabic + English.
- **Schema aggregation**: Root schema (`graphql/schema.graphql`) imports Bagisto’s upstream schema plus Najaz-specific admin + citizen modules; Lighthouse now reads from `graphql/schema.graphql`.

## Citizen Auth Schema

Located under `packages/Najaz/GraphQLAPI/graphql/app/citizen/`:

- `registration.graphql`
  - Mutation `citizenSignUp` accepts `CitizenSignUpInput` (names, gender, national id, type, password, optional contact/device info).
  - Returns `CitizenLoginResponse` (success, message, JWT token data, citizen record, plus `services` filtered by the citizen type).
- `session.graphql`
  - `citizenLogin` accepts either email or national id + password (+ optional device token).
  - `citizenLogout` is guard-protected (`citizen-api`) and returns `StatusResponse`.
- `packages/Najaz/GraphQLAPI/graphql/common.graphql`
  - Adds shared `CitizenLoginInput`, `CitizenLoginResponse`, and the `Service` type definition.

## Mutations

Namespace `Najaz\GraphQLAPI\Mutations\App\Citizen\`:

- `RegistrationMutation@signUp`
  - Validates input (unique email/phone/national id, DOB < today, password confirmation, citizen type existence).
  - Creates the citizen via `Najaz\Citizen\Repositories\CitizenRepository` (status active, auto-verified, pending identity verification).
  - Issues a JWT token through `citizen-api` guard and returns the formatted login response.
- `SessionMutation@login`
  - Validates email/national id + password.
  - Attempts guard login, applies `najaz_graphql()->authorize()` checks, stores device token.
- `SessionMutation@logout`
  - Requires `citizen-api` guard, logs out, returns localized success message.

## Configuration Notes

- `config/lighthouse.php`
  - `schema_path` now points to the root `graphql/schema.graphql`.
  - `namespaces.queries/mutations` include the Najaz GraphQL namespace.
- `composer.json`
  - Autoload gains `"Najaz\\GraphQLAPI\\": "packages/Najaz/GraphQLAPI/src"`.

## Usage Example

```graphql
mutation RegisterCitizen {
  citizenSignUp(
    input: {
      firstName: "Ali"
      middleName: "Hassan"
      lastName: "Al-Qahtani"
      gender: "male"
      email: "ali@example.com"
      phone: "+966500000001"
      nationalId: "1234567890"
      dateOfBirth: "1990-05-12"
      citizenTypeId: 1
      password: "StrongPass123"
      passwordConfirmation: "StrongPass123"
      remember: true
    }
  ) {
    success
    message
    accessToken
    citizen {
      id
      firstName
      citizenType {
        id
        name
      }
    }
  }
}
```

Same token response shape applies for `citizenLogin`.

Both mutations can now query the `services` field to immediately retrieve the list of active services tied to the citizen’s type:

```graphql
citizenLogin(input: { nationalId: "1234567890", password: "StrongPass123" }) {
  accessToken
  services {
    id
    name
    description
    sortOrder
  }
}
```

The `services` array is also available under `citizen.citizenType.services` if you prefer to traverse through the citizen object.

## Service Form Introspection (Citizen-side)

To allow citizen apps to understand exactly what fields are required to request a given service, each `Service` exposes a high-level `form` description:

```graphql
type ServiceForm {
    groups: [ServiceFormGroup!]!
}

type ServiceFormGroup {
    code: String!
    label: String!
    description: String
    sortOrder: Int
    pivotUid: String
    isNotifiable: Boolean
    customCode: String
    customName: String
    fields: [ServiceFormField!]!
}

type ServiceFormField {
    code: String!
    label: String!
    type: String!
    isRequired: Boolean!
    defaultValue: String
    validationRules: JSON
    sortOrder: Int
    options: [ServiceFormFieldOption!]
}

type ServiceFormFieldOption {
    value: String!
    label: String!
}

extend type Service {
    form: ServiceForm
}
```

Example citizen query to fetch a service plus its full form definition:

```graphql
query GetCitizenServiceWithForm {
  citizenService(id: 1) {
    id
    name
    form {
      groups {
        code
        label
        description
        sortOrder
        pivotUid
        isNotifiable
        customCode
        customName
        fields {
          code
          label
          type
          isRequired
          defaultValue
          sortOrder
          validationRules
          options {
            value
            label
          }
        }
      }
    }
  }
}
```

The `form` tree is derived from `attributeGroups`, `service_attribute_group_service` pivot data, `fields`, their `attributeType`, and its `options`, but presented in a flat, UI-friendly shape for the citizen app. 

## Citizen Identity Verification

### Schema

Citizen identity verification is exposed through a dedicated mutation and queries that work on the authenticated `citizen-api` user:

```graphql
extend type Query @guard(with: ["citizen-api"]) {
  myIdentityVerifications: [IdentityVerification!]
  myLatestIdentityVerification: IdentityVerification
}

extend type Mutation @guard(with: ["citizen-api"]) {
  requestIdentityVerification(
    input: CitizenIdentityVerificationInput! @spread
  ): IdentityVerificationResponse
}

input CitizenIdentityVerificationInput {
  notes: String
  documents: [Upload!]
}
```

`IdentityVerification` and `IdentityVerificationResponse` are shared with the admin schema and include status, notes, stored document paths, timestamps, and citizen / reviewer relations.

### Example Mutation

```graphql
mutation RequestIdentityVerification($files: [Upload!]) {
  requestIdentityVerification(
    input: {
      notes: "أرفقت صورة البطاقة من الجهتين"
      documents: $files
    }
  ) {
    success
    message
    verification {
      id
      status
      notes
      documents
      createdAt
    }
  }
}
```

### Sending Files via Postman (multipart)

File uploads use the standard `graphql-multipart-request-spec`. In Postman:

- Method: `POST`
- URL: `/graphql`
- Body: `form-data` with the following keys:

1. **operations** (Text):

```json
{
  "query": "mutation RequestIdentityVerification($files: [Upload!]) { requestIdentityVerification(input: { notes: \"أرفقت صورة البطاقة من الجهتين\", documents: $files }) { success message verification { id status notes documents } } }",
  "variables": {
    "files": [null, null]
  }
}
```

2. **map** (Text):

```json
{
  "0": ["variables.files.0"],
  "1": ["variables.files.1"]
}
```

3. **file fields** (File):

| Key | Type | Value             |
|-----|------|-------------------|
| 0   | File | front-id.jpg      |
| 1   | File | back-id.jpg       |

Postman sets `Content-Type: multipart/form-data` automatically; you must still include an `Authorization: Bearer <citizen_jwt_token>` header so the mutation runs in the context of the authenticated citizen.

