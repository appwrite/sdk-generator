cmake_minimum_required(VERSION 3.14)

project(cpp VERSION 0.0.1)

set(CMAKE_CXX_STANDARD 20)
set(CMAKE_CXX_STANDARD_REQUIRED ON)

include(FetchContent)

find_package(cpr QUIET)
if(NOT cpr_FOUND)
    set(CPR_USE_SYSTEM_CURL ON CACHE BOOL "" FORCE)
    FetchContent_Declare(cpr
        GIT_REPOSITORY https://github.com/libcpr/cpr.git
        GIT_TAG 3b15fa82ea74739b574d705fea44959b58142eb8) # sync with CI Dockerfile
    FetchContent_MakeAvailable(cpr)
endif()

find_package(nlohmann_json QUIET)
if(NOT nlohmann_json_FOUND)
    FetchContent_Declare(nlohmann_json
        GIT_REPOSITORY https://github.com/nlohmann/json.git
        GIT_TAG 9cca280a4d0ccf0c08f47a99aa71d1b0e52f8d03) # sync with CI Dockerfile
    FetchContent_MakeAvailable(nlohmann_json)
endif()

# Compiler warnings
if(MSVC)
    add_compile_options(/W4)
else()
    add_compile_options(-Wall -Wextra -Wpedantic)
endif()

# Header-only library
add_library(${PROJECT_NAME} INTERFACE)
target_include_directories(${PROJECT_NAME} INTERFACE 
    $<BUILD_INTERFACE:${CMAKE_CURRENT_SOURCE_DIR}/include>
    $<INSTALL_INTERFACE:${CMAKE_INSTALL_INCLUDEDIR}>
)
target_link_libraries(${PROJECT_NAME} INTERFACE 
    cpr::cpr 
    nlohmann_json::nlohmann_json
)

# Automated Tests
option(APPWRITE_BUILD_TESTS "Build automated tests" OFF)
option(APPWRITE_RUN_INTEGRATION "Run integration tests" OFF)
if(APPWRITE_BUILD_TESTS)
    enable_testing()
    find_package(GTest QUIET)
    if(NOT GTest_FOUND)
        FetchContent_Declare(googletest
            GIT_REPOSITORY https://github.com/google/googletest.git
            GIT_TAG f8d7d77c06936315286eb55f8de22cd23c188571) # sync with CI Dockerfile
        FetchContent_MakeAvailable(googletest)
    endif()

    add_executable(test_appwrite 
        tests/tests.cpp
        tests/model_tests.cpp
    )
    target_link_libraries(test_appwrite PRIVATE ${PROJECT_NAME} GTest::gtest)
    
    add_executable(integration_test 
        tests/integration.cpp
        tests/integration_logic.cpp
    )
    if(APPWRITE_RUN_INTEGRATION)
        target_compile_definitions(integration_test PRIVATE APPWRITE_RUN_INTEGRATION)
    endif()
    target_link_libraries(integration_test PRIVATE ${PROJECT_NAME} nlohmann_json::nlohmann_json)

    add_test(NAME sdk_tests COMMAND test_appwrite)
    add_test(NAME integration_tests COMMAND integration_test)
endif()

# Installation
include(GNUInstallDirs)
install(DIRECTORY include/appwrite DESTINATION ${CMAKE_INSTALL_INCLUDEDIR})

