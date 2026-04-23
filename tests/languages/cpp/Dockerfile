FROM alpine:3.19

RUN apk add --no-cache \
    build-base \
    cmake \
    curl-dev \
    git \
    openssl-dev

# Pre-build and install cpr
RUN git clone https://github.com/libcpr/cpr.git \
    && cd cpr && git checkout 3b15fa82ea74739b574d705fea44959b58142eb8 && cd .. \  # v1.10.5 — matches CI workflow
    && cmake -S cpr -B cpr/build \
        -DCPR_BUILD_TESTS=OFF \
        -DCPR_USE_SYSTEM_CURL=ON \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --build cpr/build -j$(nproc) \
    && cmake --install cpr/build \
    && rm -rf cpr

# Pre-build and install nlohmann/json
RUN git clone https://github.com/nlohmann/json.git \
    && cd json && git checkout 9cca280a4d0ccf0c08f47a99aa71d1b0e52f8d03 && cd .. \
    && cmake -S json -B json/build \
        -DJSON_BuildTests=OFF \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --install json/build \
    && rm -rf json

# Pre-build and install googletest
RUN git clone https://github.com/google/googletest.git \
    && cd googletest && git checkout f8d7d77c06936315286eb55f8de22cd23c188571 && cd .. \
    && cmake -S googletest -B googletest/build \
        -DBUILD_GMOCK=OFF \
        -DCMAKE_INSTALL_PREFIX=/usr/local \
    && cmake --build googletest/build -j$(nproc) \
    && cmake --install googletest/build \
    && rm -rf googletest

WORKDIR /app
