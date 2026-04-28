import 'dart:io';
import 'dart:isolate';
import 'dart:ui';

import 'package:device_info_plus/device_info_plus.dart';
import 'package:everlms/feature/order/data/models/order_history.dart';
import 'package:flutter_downloader/flutter_downloader.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class DownloadNotifier extends Notifier<void> {
  @override
  void build() {}

  final _port = ReceivePort();
  String _localPath = '';
  bool _permissionReady = false;

  Future<void> initialize() async {
    _bindBackgroundIsolate();
    await _prepare();
  }

  Future<void> downloadFile(OrderHistory history) async {
    if (!_permissionReady) {
      _permissionReady = await _checkPermission();
    }
    if (!_permissionReady) return;

    final invoiceLink = history.invoiceLink;
    if (invoiceLink == null || invoiceLink.isEmpty) return;

    await FlutterDownloader.enqueue(
      url: invoiceLink,
      savedDir: _localPath,
      fileName: '${history.title ?? 'invoice'}.pdf',
    );
  }

  void _bindBackgroundIsolate() {
    final isSuccess = IsolateNameServer.registerPortWithName(
      _port.sendPort,
      'downloader_send_port',
    );
    if (!isSuccess) {
      IsolateNameServer.removePortNameMapping('downloader_send_port');
      IsolateNameServer.registerPortWithName(
        _port.sendPort,
        'downloader_send_port',
      );
    }
  }

  @pragma('vm:entry-point')
  static void downloadCallback(String id, int status, int progress) {
    final send = IsolateNameServer.lookupPortByName('downloader_send_port');
    send?.send([id, status, progress]);
  }

  Future<void> _prepare() async {
    _permissionReady = await _checkPermission();
    _localPath = await _getSaveDir();
  }

  Future<String> _getSaveDir() async {
    final directory = Platform.isAndroid
        ? await getExternalStorageDirectory()
        : await getApplicationDocumentsDirectory();
    final savedDir = directory?.path ?? '';
    final dir = Directory(savedDir);
    if (!dir.existsSync()) {
      await dir.create(recursive: true);
    }
    return savedDir;
  }

  Future<bool> _checkPermission() async {
    if (Platform.isAndroid) {
      final info = await DeviceInfoPlugin().androidInfo;
      if (info.version.sdkInt >= 33) return true;
      final status = await Permission.storage.status;
      if (status != PermissionStatus.granted) {
        final result = await Permission.storage.request();
        return result == PermissionStatus.granted;
      }
      return true;
    }
    return true;
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final downloadProvider = NotifierProvider<DownloadNotifier, void>(
  DownloadNotifier.new,
);
