package io.appwrite;

import io.appwrite.coroutines.CoroutineCallback;
import io.appwrite.exceptions.AppwriteException;
import io.appwrite.models.InputFile;
import io.appwrite.models.Profile;
import io.appwrite.services.General;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.concurrent.CompletableFuture;
import java.util.concurrent.TimeUnit;
import kotlin.coroutines.intrinsics.IntrinsicsKt;

public final class JavaInterop {
    private JavaInterop() {
    }

    public static List<String> run(General general, String file) throws Exception {
        List<String> lines = new ArrayList<>();

        lines.add(ID.unique());
        lines.add(Permission.read(Role.any()));
        lines.add(Query.limit(1));
        lines.add(Query.count());
        lines.add(Operator.increment());
        lines.add(Operator.increment(5));

        try {
            general.validatePath("0", "", new CoroutineCallback<>((result, error) -> {
            }));
            lines.add("Empty path parameter was accepted");
        } catch (AppwriteException e) {
            lines.add(e.getMessage());
        }

        CompletableFuture<Profile<?>> profile = new CompletableFuture<>();
        try {
            Object returned = general.uploadGeneric(InputFile.fromPath(file), Map.class, new CoroutineCallback<>((result, error) -> {
                if (error != null) {
                    profile.completeExceptionally(error);
                } else {
                    profile.complete(result);
                }
            }));
            if (returned != IntrinsicsKt.getCOROUTINE_SUSPENDED()) {
                profile.complete((Profile<?>) returned);
            }
        } catch (AppwriteException e) {
            profile.completeExceptionally(e);
        }
        Map<?, ?> data = (Map<?, ?>) profile.get(30, TimeUnit.SECONDS).getPrefs().getData();
        lines.add((String) data.get("result"));

        return lines;
    }
}
