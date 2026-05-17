extends Control

func _on_ping_click():
	$Status.text += str(await Appwrite.ping())
