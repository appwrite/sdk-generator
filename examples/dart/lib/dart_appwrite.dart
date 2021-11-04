library dart_appwrite;

import 'dart:async';
import 'package:http/http.dart' as http;
import 'src/enums.dart';
import 'src/client.dart';
import 'src/response.dart';
import 'src/service.dart';

export 'src/response.dart';
export 'src/client.dart';
export 'src/exception.dart';
export 'package:http/http.dart' show MultipartFile;

part 'services/account.dart';
part 'services/avatars.dart';
part 'services/database.dart';
part 'services/functions.dart';
part 'services/health.dart';
part 'services/locale.dart';
part 'services/storage.dart';
part 'services/teams.dart';
part 'services/users.dart';
