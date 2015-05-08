package com.tx;

import backtype.storm.topology.IRichSpout;
import backtype.storm.spout.SpoutOutputCollector;
import backtype.storm.task.TopologyContext;
import backtype.storm.utils.Utils;
import backtype.storm.tuple.Values;
import backtype.storm.tuple.Fields;
import backtype.storm.topology.OutputFieldsDeclarer;
import java.util.Map;
import java.util.HashMap;
import java.util.Random;
import org.apache.log4j.Logger;
import backtype.storm.Config;

/**
 * Hello world!
 *
 */
public class AppSpout implements IRichSpout
{

    SpoutOutputCollector _collector;

    public void open(Map conf, TopologyContext context, SpoutOutputCollector collector)
    {
        _collector = collector;
    }

    public void close()
    {
    }

    public void activate()
    {
    }

    public void deactivate()
    {
    }

    public void nextTuple()
    {
        Utils.sleep(100);
        final String[] words = new String[] {"wo", "cao", "jiang", "hu"};
        final Random rand = new Random();
        final String word = words[rand.nextInt(words.length)];

        /*
         * emit() 最多参数 String stream id, List<Object> tuple, Object messageId
         */
        _collector.emit(new Values(word));
    }

    public void ack(Object msgId)
    {
    }

    public void fail(Object msgId)
    {
    }

    public void declareOutputFields(OutputFieldsDeclarer declarer)
    {
        declarer.declare(new Fields("whatIsThis")); //@TODO
    }

    public Map<String, Object> getComponentConfiguration()
    {
        if(false) {
            // not distribute
            Map<String, Object> ret = new HashMap<String, Object>();
            ret.put(Config.TOPOLOGY_MAX_TASK_PARALLELISM, 1);
            return ret;
        } else {
            // distribute
            return null;
        }
    }
}
