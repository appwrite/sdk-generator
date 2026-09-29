# Push: rules applied to apps that shrink with R8.
#
# The HiveMQ MQTT client bundles Netty, which references optional classes that are never loaded
# on Android: native transports, the HTTP/WebSocket codecs, proxy handlers, alternative TLS
# providers (tcnative, BouncyCastle, Conscrypt, Jetty ALPN) and logging backends.
-dontwarn io.netty.channel.epoll.**
-dontwarn io.netty.channel.kqueue.**
-dontwarn io.netty.handler.codec.http.**
-dontwarn io.netty.handler.proxy.**
-dontwarn io.netty.internal.tcnative.**
-dontwarn org.bouncycastle.**
-dontwarn org.conscrypt.**
-dontwarn org.eclipse.jetty.**
-dontwarn org.apache.log4j.**
-dontwarn org.apache.logging.log4j.**
-dontwarn org.slf4j.**
-dontwarn reactor.blockhound.**

# Netty and JCTools read their own members by reflection (HiveMQ's guidance for Android).
-keepclassmembernames class io.netty.** { *; }
-keepclassmembers class org.jctools.** { *; }
