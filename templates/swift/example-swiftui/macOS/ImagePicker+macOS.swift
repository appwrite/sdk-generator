import AppKit

extension ImagePicker {
    static func present() {
        let dialog = NSOpenPanel();

        dialog.title                   = "Choose an image"
        dialog.showsResizeIndicator    = true
        dialog.showsHiddenFiles        = false
        dialog.allowsMultipleSelection = false
        dialog.canChooseDirectories    = false
        dialog.allowedFileTypes        = ["png", "jpg", "jpeg", "gif", "tiff"]

        if (dialog.runModal() ==  NSApplication.ModalResponse.OK) {
            guard let result = dialog.url else {
                return
            }
            
            
            // path contains the file path e.g
            // /Users/ourcodeworld/Desktop/tiger.jpeg
            
        } else {
            // User clicked on "Cancel"
            return
        }
    }
}
