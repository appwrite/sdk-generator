FROM alpine:3.19

RUN apk add --no-cache \
    build-base \
    cmake \
    curl-dev \
    git \
    openssl-dev

# Pre-build and install cpr
RUN git clone --depth=1 --branch 1.10.5 https://github.com/libcpr/cpr.git \
    && cmake -S cpr -B cpr/build \
        -DCPR_BUILD_TESTS=OFF \
        -DCPR_USE_SYSTEM_CURL=ON \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --build cpr/build -j$(nproc) \
    && cmake --install cpr/build \
    && rm -rf cpr

# Pre-build and install nlohmann/json
RUN git clone --depth=1 --branch v3.11.3 https://github.com/nlohmann/json.git \
    && cmake -S json -B json/build \
        -DJSON_BuildTests=OFF \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --install json/build \
    && rm -rf json

# Pre-build and install googletest
RUN git clone --depth=1 --branch v1.14.0 https://github.com/google/googletest.git \
    && cmake -S googletest -B googletest/build \
        -DBUILD_GMOCK=OFF \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --build googletest/build -j$(nproc) \
    && cmake --install googletest/build \
    && rm -rf googletest

WORKDIR /app
