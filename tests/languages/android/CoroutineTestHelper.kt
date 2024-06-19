package io.appwrite


import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.TestDispatcher
import kotlinx.coroutines.test.TestScope
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain

object CoroutineTestUtils {

    private lateinit var dispatcher: TestDispatcher

    @JvmStatic
    @OptIn(ExperimentalCoroutinesApi::class)
    fun setTestMainDispatcher(): TestDispatcher {
        dispatcher = StandardTestDispatcher()
        Dispatchers.setMain(dispatcher)
        return dispatcher
    }

    @JvmStatic
    @OptIn(ExperimentalCoroutinesApi::class)
    fun resetTestMainDispatcher() = Dispatchers.resetMain()

    @JvmStatic
    fun scopeFor(dispatcher: TestDispatcher) = TestScope(dispatcher)

    @JvmStatic
    fun runTest(scope: TestScope, block: suspend () -> Unit) {
        scope.runTest { block() }
    }
}
