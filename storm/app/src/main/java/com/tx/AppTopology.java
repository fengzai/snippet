package com.tx;

import backtype.storm.Config;
import backtype.storm.LocalCluster;
import backtype.storm.StormSubmitter;
import backtype.storm.topology.TopologyBuilder;
import backtype.storm.utils.Utils;

/**
 *
 */
public class AppTopology
{
    public static void main( String[] args )
    {
        TopologyBuilder builder = new TopologyBuilder();
        
        //set method: p1 component id, p2 object, p3 thread count
        builder.setSpout("sid", new AppSpout(), 3);        
        builder.setBolt("bid", new AppBolt(), 3)
                .shuffleGrouping("sid"); //receive from which component id, here receive from sid
        builder.setBolt("bid1", new AppBolt(), 3)
                .shuffleGrouping("bid");
                
        Config conf = new Config();
        conf.setDebug(true);

        try{
            if(args!=null && args.length > 0) {
                conf.setNumWorkers(3);

                StormSubmitter.submitTopology(args[0], conf, builder.createTopology());
            } else {

                LocalCluster cluster = new LocalCluster();
                cluster.submitTopology("justLocal", conf, builder.createTopology());
                Utils.sleep(10000);
                cluster.killTopology("justLocal");
                cluster.shutdown();    
            }
        }catch(Exception e){
            System.out.println("FUCK GFW");
        }
    }
}
