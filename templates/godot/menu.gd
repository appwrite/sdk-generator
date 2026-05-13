extends Control

func _on_ping_click():
	$Status.text += await Appwrite.ping()
