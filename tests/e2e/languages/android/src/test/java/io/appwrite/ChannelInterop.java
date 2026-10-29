package io.appwrite;

import java.util.Arrays;
import java.util.List;

public final class ChannelInterop {
    private ChannelInterop() {
    }

    public static List<String> run() {
        return Arrays.asList(Channel.database("db1").toString(), Channel.account());
    }
}
