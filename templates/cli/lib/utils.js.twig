const fs = require("fs");
const path = require("path");

function getAllFiles(folder) {
    const files = [];
    for(const pathDir of fs.readdirSync(folder)) {
        const pathAbsolute = path.join(folder, pathDir);
        if (fs.statSync(pathAbsolute).isDirectory()) {
            files.push(...getAllFiles(pathAbsolute));
        } else {
            files.push(pathAbsolute);
        }
    }
    return files;
}

const checkDeployConditions = (localConfig) => {
    if (Object.keys(localConfig.data).length === 0) {
        throw new Error("No appwrite.json file found in the current directory. This command must be run in the folder holding your appwrite.json file. Please run this command again in the folder containing your appwrite.json file, or run appwrite init project.");
    }
}

module.exports = {
    getAllFiles,
    checkDeployConditions
};