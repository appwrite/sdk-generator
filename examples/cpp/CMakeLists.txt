cmake_minimum_required(VERSION 3.14)

project(cpp VERSION 0.0.1)

set(CMAKE_CXX_STANDARD 20)
set(CMAKE_CXX_STANDARD_REQUIRED ON)

include(FetchContent)

find_package(cpr QUIET)
if(NOT cpr_FOUND)
    FetchContent_Declare(cpr
        GIT_REPOSITORY https://github.com/libcpr/cpr.git
        GIT_TAG 1.10.5)
    FetchContent_MakeAvailable(cpr)
endif()

find_package(nlohmann_json QUIET)
if(NOT nlohmann_json_FOUND)
    FetchContent_Declare(nlohmann_json
        GIT_REPOSITORY https://github.com/nlohmann/json.git
        GIT_TAG v3.11.3)
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
if(APPWRITE_BUILD_TESTS)
    enable_testing()
    find_package(GTest QUIET)
    if(NOT GTest_FOUND)
        FetchContent_Declare(googletest
            GIT_REPOSITORY https://github.com/google/googletest.git
            GIT_TAG v1.14.0)
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

