import { sdkForConsole, sdkForProject } from "./sdks.js";
import {
  Client,
  Console,
  Databases,
  Functions,
  Messaging,
  Organizations,
  Projects,
  Proxy,
  Sites,
  Storage,
  TablesDB,
  Teams,
} from "@appwrite.io/console";

export const getConsoleService = async (sdk?: Client): Promise<Console> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Console(client);
};

export const getDatabasesService = async (sdk?: Client): Promise<Databases> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Databases(client);
};

export const getFunctionsService = async (sdk?: Client): Promise<Functions> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Functions(client);
};

export const getMessagingService = async (sdk?: Client): Promise<Messaging> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Messaging(client);
};

export const getOrganizationsService = async (
  sdk?: Client,
): Promise<Organizations> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Organizations(client);
};

export const getProjectsService = async (sdk?: Client): Promise<Projects> => {
  const client = !sdk ? await sdkForConsole() : sdk;
  return new Projects(client);
};

export const getProxyService = async (sdk?: Client): Promise<Proxy> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Proxy(client);
};

export const getSitesService = async (sdk?: Client): Promise<Sites> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Sites(client);
};

export const getStorageService = async (sdk?: Client): Promise<Storage> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Storage(client);
};

export const getTablesDBService = async (sdk?: Client): Promise<TablesDB> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new TablesDB(client);
};

export const getTeamsService = async (sdk?: Client): Promise<Teams> => {
  const client = !sdk ? await sdkForProject() : sdk;
  return new Teams(client);
};
