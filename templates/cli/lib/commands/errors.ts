/**
 * Error thrown when destructive changes are detected during push operations
 * and the force flag is not enabled.
 */
export class DestructiveChangeError extends Error {
  constructor(
    message: string,
    private metadata: {
      changes: Array<{
        type: string;
        resource: string;
        field: string;
        oldValue?: unknown;
        newValue?: unknown;
      }>;
      affectedResources: number;
    },
  ) {
    super(message);
    this.name = "DestructiveChangeError";
    Error.captureStackTrace(this, DestructiveChangeError);
  }

  /**
   * Get detailed metadata about the destructive changes
   */
  public getMetadata() {
    return this.metadata;
  }
}

/**
 * Error thrown when configuration validation fails
 */
export class ConfigValidationError extends Error {
  constructor(
    message: string,
    private validationErrors: Array<{
      path: string;
      message: string;
    }>,
  ) {
    super(message);
    this.name = "ConfigValidationError";
    Error.captureStackTrace(this, ConfigValidationError);
  }

  /**
   * Get detailed validation errors
   */
  public getValidationErrors() {
    return this.validationErrors;
  }
}

/**
 * Error thrown when a requested resource is not found
 */
export class ResourceNotFoundError extends Error {
  constructor(
    message: string,
    public resourceType: string,
    public resourceId: string,
  ) {
    super(message);
    this.name = "ResourceNotFoundError";
    Error.captureStackTrace(this, ResourceNotFoundError);
  }
}

/**
 * Error thrown when authentication fails or is missing
 */
export class AuthenticationError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "AuthenticationError";
    Error.captureStackTrace(this, AuthenticationError);
  }
}

/**
 * Error thrown when project is not initialized
 */
export class ProjectNotInitializedError extends Error {
  constructor(
    message: string = "Project configuration not found. Project must be initialized first.",
  ) {
    super(message);
    this.name = "ProjectNotInitializedError";
    Error.captureStackTrace(this, ProjectNotInitializedError);
  }
}
