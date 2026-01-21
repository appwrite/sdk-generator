import * as fs from "fs";
import * as path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const TEMPLATES_DIR = path.join(__dirname, "templates");

/**
 * Loads a template file from the templates directory.
 * @param templateName - The name of the template file (e.g., "types.ts.tpl")
 * @returns The template content as a string
 */
export function loadTemplate(templateName: string): string {
  const templatePath = path.join(TEMPLATES_DIR, templateName);
  return fs.readFileSync(templatePath, "utf-8");
}

/**
 * Renders a template by replacing placeholders with values.
 * Supports both simple variables ({{variableName}}) and section markers ({{SECTION_NAME}}).
 *
 * @param template - The template string with placeholders
 * @param variables - Record of variable names to their values
 * @returns The rendered template with all placeholders replaced
 */
export function renderTemplate(
  template: string,
  variables: Record<string, string>,
): string {
  return template.replace(/\{\{(\w+)\}\}/g, (match, key) => {
    if (key in variables) {
      return variables[key];
    }
    return match;
  });
}
