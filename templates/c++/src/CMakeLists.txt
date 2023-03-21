# Set the project name
project (appwrite)

# Add a library with the above sources

file(GLOB SOURCES "lib/*.cpp")
file(GLOB SOURCES1 "services/*.cpp")


include(FetchContent)
FetchContent_Declare(cpr GIT_REPOSITORY https://github.com/libcpr/cpr.git GIT_TAG f4622efcb59d84071ae11404ae61bd821c1c344b) # the commit hash for 1.6.2
FetchContent_MakeAvailable(cpr)


add_library(${PROJECT_NAME} STATIC ${SOURCES} ${SOURCES1})


target_include_directories( ${PROJECT_NAME}
PUBLIC ${PROJECT_SOURCE_DIR}/include
PUBLIC ${PROJECT_SOURCE_DIR}/services
)

target_link_libraries(${PROJECT_NAME} PUBLIC cpr::cpr)